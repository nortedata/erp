<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\WoocommerceUtil;
use App\Models\Produto;
use App\Models\Empresa;
use App\Models\ProdutoVariacao;
use App\Models\UnidadeMedida;
use App\Models\PadraoTributacaoProduto;
use App\Models\CategoriaProduto;
use App\Models\CategoriaWoocommerce;
use App\Models\WoocommerceItemPedido;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\ProdutoLocalizacao;

class WoocommerceProdutoController extends Controller
{

    protected $util;
    protected $endpoint = 'products';
    public function __construct(WoocommerceUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request){
        $woocommerceClient = $this->util->getConfig($request->empresa_id);

        $data = $woocommerceClient->get('products/categories');
        foreach($data as $c){
            $item = CategoriaWoocommerce::where('empresa_id', $request->empresa_id)
            ->where('_id', $c->id)->first();

            if($item == null){
                CategoriaWoocommerce::create([
                    '_id' => $c->id,
                    'empresa_id' => $request->empresa_id,
                    'nome' => $c->name,
                    'slug' => $c->slug,
                    'descricao' => $c->description
                ]);
            }
        }

        $wooProdutos = $woocommerceClient->get($this->endpoint);
        $produtosIsert = [];
        // dd($wooProdutos[0]);
        foreach($wooProdutos as $p){
            $res = $this->validaProdutoCadastrado($p, $request->empresa_id);
            if(is_array($res)){
                $produtosIsert[] = $res;
            }
        }

        if(sizeof($produtosIsert) > 0){
            $empresa = Empresa::findOrFail($request->empresa_id);
            $listaCTSCSOSN = Produto::listaCSOSN();
            if ($empresa->tributacao == 'Regime Normal') {
                $listaCTSCSOSN = Produto::listaCST();
            }
            $padraoTributacao = PadraoTributacaoProduto::where('empresa_id', request()->empresa_id)->where('padrao', 1)
            ->first();
            $padroes = PadraoTributacaoProduto::where('empresa_id', request()->empresa_id)->get();
            $categorias = CategoriaProduto::where('empresa_id', request()->empresa_id)->get();
            $unidades = UnidadeMedida::where('empresa_id', request()->empresa_id)
            ->where('status', 1)->get();
            
            return view('woocommerce_produtos.create_produtos', 
                compact('produtosIsert', 'padraoTributacao', 'listaCTSCSOSN', 'padroes', 'categorias', 'unidades'));
        }else{
            $data = Produto::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->nome), function ($q) use ($request) {
                return $q->where('nome', 'LIKE', "%$request->nome%");
            })
            ->where('woocommerce_id', '!=', null)
            ->paginate(env("PAGINACAO"));

            foreach($wooProdutos as $wp){
                foreach($data as $p){
                    if($p->woocommerce_id == $wp->id){
                        if(sizeof($wp->images) > 0){
                            $p->img_aux = $wp->images[0]->src;
                        }
                    }
                }
            }
            return view('woocommerce_produtos.index', compact('data')); 

        }
    }

    private function validaProdutoCadastrado($wooProduto, $empresa_id){
        $produto = Produto::where('empresa_id', $empresa_id)
        ->where('woocommerce_id', $wooProduto->id)
        ->first();

        if($produto != null){
            $this->atualizaProduto($wooProduto, $produto);
            return true;
        }

        $categorias = [];
        foreach($wooProduto->categories as $c){

            $item = CategoriaWoocommerce::where('empresa_id', $empresa_id)
            ->where('_id', $c->id)->first();

            if($item == null){
                CategoriaWoocommerce::create([
                    '_id' => $c->id,
                    'empresa_id' => $empresa_id,
                    'nome' => $c->name,
                    'slug' => $c->slug,
                    'descricao' => $c->description
                ]);
            }

            $categoria = CategoriaWoocommerce::where('_id', $c->id)
            ->first();

            if($categoria){
                $categorias[] = $categoria->id;
            }
        }
        $dataProduto = [
            'empresa_id' => $empresa_id,
            'nome' => $wooProduto->name,
            'valor_venda' => $wooProduto->price,
            'woocommerce_id' => $wooProduto->id,
            'woocommerce_valor' => $wooProduto->price,
            'woocommerce_slug' => $wooProduto->slug,
            'woocommerce_link' => $wooProduto->permalink,
            'woocommerce_type' => $wooProduto->type,
            'woocommerce_status' => $wooProduto->status,
            'woocommerce_descricao' => $wooProduto->description,
            'woocommerce_stock_status' => $wooProduto->stock_status,
            'categorias_woocommerce' => json_encode($categorias),
            'estoque' => $wooProduto->stock_quantity ? $wooProduto->stock_quantity : null

        ];

        if(sizeof($wooProduto->variations) > 0){
            $variacoes = [];
            $woocommerceClient = $this->util->getConfig($empresa_id);
            foreach($wooProduto->variations as $v){
                $wooVariation = $woocommerceClient->get("products/$wooProduto->id/variations/$v");
                // dd($wooVariation);
                $dataVariacao = [
                    '_id' => $wooVariation->id,
                    'quantidade' => $wooVariation->stock_quantity,
                    'valor' => $wooVariation->price,
                    'nome' => $wooVariation->attributes[0]->name,
                    'valor_nome' => $wooVariation->name,
                    'codigo_barras' => $wooVariation->global_unique_id
                ];
                array_push($variacoes, $dataVariacao);
            }

            $dataProduto['variacoes'] = $variacoes;
        }
        return $dataProduto;
    }

    private function atualizaProduto($wooProduto, $produto){

        $produto->woocommerce_slug = $wooProduto->slug;
        $produto->woocommerce_link = $wooProduto->permalink;
        $produto->woocommerce_valor = $wooProduto->price;
        $produto->woocommerce_type = $wooProduto->type;
        $produto->woocommerce_status = $wooProduto->status;
        $produto->woocommerce_descricao = $wooProduto->description;
        $produto->woocommerce_stock_status = $wooProduto->stock_status;
        $produto->peso = $wooProduto->weight ?? 0;
        if($wooProduto->dimensions){
            $produto->comprimento = $wooProduto->dimensions->length ?? 0;
            $produto->largura = $wooProduto->dimensions->width ?? 0;
            $produto->altura = $wooProduto->dimensions->height ?? 0;
        }
        $produto->save();
    }

    public function store(Request $request){
        DB::transaction(function () use ($request) {
            $contInserts = 0;
            try{
                $itensNull = WoocommerceItemPedido::select('woocommerce_item_pedidos.*')
                ->join('woocommerce_pedidos', 'woocommerce_item_pedidos.pedido_id', '=', 'woocommerce_pedidos.id')
                ->where('woocommerce_pedidos.empresa_id', $request->empresa_id)
                ->where('woocommerce_item_pedidos.produto_id', null)
                ->get();
                for($i=0; $i<sizeof($request->woocommerce_id); $i++){

                    $data = [
                        'woocommerce_id' => $request->woocommerce_id[$i],
                        'nome' => $request->nome[$i],
                        'valor_unitario' => __convert_value_bd($request->valor_venda[$i]),
                        'woocommerce_valor' => __convert_value_bd($request->woocommerce_valor[$i]),
                        'valor_compra' => $request->valor_compra[$i] ? __convert_value_bd($request->valor_compra[$i]) : 0,
                        'codigo_barras' => $request->codigo_barras[$i],
                        'ncm' => $request->ncm[$i],
                        'unidade' => $request->unidade[$i],
                        'gerenciar_estoque' => $request->gerenciar_estoque[$i],
                        'categoria_id' => $request->categoria_id[$i],
                        'cest' => $request->cest[$i],
                        'cfop_estadual' => $request->cfop_estadual[$i],
                        'cfop_outro_estado' => $request->cfop_outro_estado[$i],
                        'perc_icms' => __convert_value_bd($request->perc_icms[$i]),
                        'perc_pis' => __convert_value_bd($request->perc_pis[$i]),
                        'perc_cofins' => __convert_value_bd($request->perc_cofins[$i]),
                        'perc_ipi' => __convert_value_bd($request->perc_ipi[$i]),
                        'perc_red_bc' => $request->perc_red_bc[$i] ? __convert_value_bd($request->perc_red_bc[$i]) : 0,
                        'cst_csosn' => $request->cst_csosn[$i],
                        'cst_pis' => $request->cst_pis[$i],
                        'cst_cofins' => $request->cst_cofins[$i],
                        'cst_ipi' => $request->cst_ipi[$i],
                        'cEnq' => $request->cEnq[$i],
                        'empresa_id' => $request->empresa_id,
                        'woocommerce_slug' => $request->woocommerce_slug[$i],
                        'woocommerce_link' => $request->woocommerce_link[$i],
                        'woocommerce_type' => $request->woocommerce_type[$i],
                        'woocommerce_status' => $request->woocommerce_status[$i],
                        'woocommerce_descricao' => $request->woocommerce_descricao[$i],
                        'woocommerce_stock_status' => $request->woocommerce_stock_status[$i],
                        'categorias_woocommerce' => $request->categorias_woocommerce[$i]
                    ];

                    $produto = Produto::create($data);
                    ProdutoLocalizacao::updateOrCreate([
                        'produto_id' => $produto->id, 
                        'localizacao_id' => $request->local_id
                    ]);

                    if($request->woocommerce_id_row){
                        for($j=0; $j<sizeof($request->woocommerce_id_row); $j++){
                            if($request->woocommerce_id[$i] == $request->variacao_nome[$j]){

                                $dataVariacao = [
                                    'produto_id' => $produto->id,
                                    'descricao' => $request->variacao_nome[$i],
                                    'valor' => __convert_value_bd($request->variacao_valor[$i]),
                                    'codigo_barras' => $request->variacao_codigo_barras[$i],
                                    'referencia' => '',
                                    'imagem' => ''
                                ];
                                $variacao = ProdutoVariacao::create($dataVariacao);

                            }
                        }
                    }

                    if($request->estoque[$i] && $request->estoque[$i] > 0){
                        $this->util->incrementaEstoque($produto->id, $request->estoque[$i], null, $request->local_id);
                    }

                    foreach($itensNull as $it){
                        if($produto->nome == $it->item_nome){
                            $it->produto_id = $produto->id;
                            $it->save();
                        }
                    }
                    $contInserts++;
                }
                session()->flash("flash_success", "Total de produtos inseridos: $contInserts");

            }catch(\Exception $e){
                // echo $e->getMessage();
                // die;
                session()->flash("flash_error", $e->getMessage());
            }

        });
return redirect()->route('woocommerce-produtos.index');
}

public function edit($id){
    $item = Produto::findOrFail($id);
    $woocommerceClient = $this->util->getConfig(request()->empresa_id);

    $wooProduto = $woocommerceClient->get($this->endpoint."/$item->woocommerce_id");
    $categorias = CategoriaWoocommerce::where('empresa_id', request()->empresa_id)->get();

    $item->categorias_woocommerce = $item->categorias_woocommerce ? json_decode($item->categorias_woocommerce) : [];
    return view('woocommerce_produtos.edit', compact('item', 'wooProduto', 'categorias'));
}

public function update(Request $request, $id){
    $item = Produto::findOrFail($id);
    $woocommerceClient = $this->util->getConfig(request()->empresa_id);

    $data = [
        'name' => $request->nome,
        'slug' => $request->woocommerce_slug,
        'stock_status' => $request->woocommerce_stock_status,
        'status' => $request->woocommerce_status,
        'price' => __convert_value_bd($request->woocommerce_valor),
        'description' => $request->woocommerce_descricao,
        'weight' => $request->peso
    ];
    if($request->comprimento){
        $data['dimensions']['length'] = $request->comprimento;
    }
    if($request->largura){
        $data['dimensions']['width'] = $request->largura;
    }
    if($request->altura){
        $data['dimensions']['height'] = $request->altura;
    }
    try{

        $wooProduto = $woocommerceClient->put($this->endpoint."/$item->woocommerce_id", $data);
        if($wooProduto->id){
            $item->nome = $request->nome;
            $item->woocommerce_slug = $request->woocommerce_slug;
            $item->woocommerce_descricao = $request->description;
            $item->woocommerce_stock_status = $request->woocommerce_stock_status;
            $item->woocommerce_valor = __convert_value_bd($request->woocommerce_valor);

            $item->peso = $request->peso;
            $item->largura = $request->largura;
            $item->comprimento = $request->comprimento;
            $item->altura = $request->altura;
            $item->save();

            session()->flash("flash_success", "Produto atualizado!");
            return redirect()->route('woocommerce-produtos.index');

        }
    }catch(\Exception $e){
        echo $e->getMessage();
        die;
        session()->flash("flash_error", "Algo deu errado ao atualizar: " . $e->getMessage());
        return redirect()->back();
    }

}

public function galery($id){
    $item = Produto::findOrFail($id);
    $woocommerceClient = $this->util->getConfig($item->empresa_id);
    $wooProduto = $woocommerceClient->get($this->endpoint."/$item->woocommerce_id");
        // dd($wooProduto);
    return view('woocommerce_produtos.galery', compact('item', 'wooProduto'));

}

public function galeryStore(Request $request){
    $item = Produto::findOrFail($request->produto_id);
    $woocommerceClient = $this->util->getConfig($request->empresa_id);
    try{
        if ($request->hasFile('image')) {
            if (!is_dir(public_path('uploads') . 'image_temp')) {
                mkdir(public_path('uploads') . 'image_temp', 0777, true);
            }

            $this->clearFolder(public_path('uploads'). '/image_temp');

            $wooProduto = $woocommerceClient->get($this->endpoint."/$item->woocommerce_id");

            $file = $request->image;
            $ext = $file->getClientOriginalExtension();
            $file_name = Str::random(20) . ".$ext";

            $file->move(public_path('uploads'). '/image_temp', $file_name);
            $image = env('APP_URL') . '/uploads/image_temp/'.$file_name;

                // $image = 'https://pngimg.com/d/free_PNG90785.png';
                // $image = 'https://w7.pngwing.com/pngs/239/953/png-transparent-strawberry-strawberries-fruit-summer-fresh-sweet-food-png-free-psd-cutout.png';

            $data['images'] = [];
            foreach($wooProduto->images as $i){
                $data['images'][] = ['src' => $i->src];
            }
            $data['images'][] = ['src' => $image];
                // dd($data);
            $wooProduto = $woocommerceClient->put($this->endpoint."/$item->woocommerce_id", $data);
            session()->flash("flash_success", "Imagem adicionada!");
        }
    }catch(\Exception $e){
            // echo $e->getMessage();
            // die;
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }
    return redirect()->back();          

}

private function clearFolder($destino){
    $files = glob($destino."/*");
    foreach($files as $file){ 
        if(is_file($file)) unlink($file); 
    }
}

public function galeryDelete(Request $request){
    $produto_id = $request->produto_id;
    $woocommerceClient = $this->util->getConfig($request->empresa_id);
    try{
        $data['images'] = [];
        if($request->picture){
            for($i=0; $i<sizeof($request->picture); $i++){
                $data['images'][] = [ 'src' => $request->picture[$i]];
            }
        }

        $wooProduto = $woocommerceClient->put($this->endpoint."/$produto_id", $data);
        session()->flash("flash_success", "Imagem removida!");

    }catch(\Exception $e){
            // echo $e->getMessage();
            // die;
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }
    return redirect()->back();
}

}
