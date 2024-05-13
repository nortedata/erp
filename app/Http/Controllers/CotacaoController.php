<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotacao;
use App\Models\ItemCotacao;
use App\Models\Fornecedor;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Mail;

class CotacaoController extends Controller
{
    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $fornecedor_id = $request->get('fornecedor_id');
        $estado = $request->get('estado');
        $referencia = $request->get('referencia');
        $gerado_compra = $request->get('gerado_compra');
        $fornecedor = null;

        $data = Cotacao::where('empresa_id', request()->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($fornecedor_id), function ($query) use ($fornecedor_id) {
            return $query->where('fornecedor_id', $fornecedor_id);
        })
        ->when($estado != "", function ($query) use ($estado) {
            return $query->where('estado', $estado);
        })
        ->when($referencia != "", function ($query) use ($referencia) {
            return $query->where('referencia', 'LIKE', "%$referencia%");
        })
        ->when($gerado_compra != "", function ($query) use ($gerado_compra) {
            if($gerado_compra == 1){
                return $query->where('nfe_id', '>', 0);
            }
            return $query->where('nfe_id', null);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"));

        if(!empty($fornecedor_id)){
            $fornecedor = Fornecedor::findOrFail($fornecedor_id);
        }
        return view('cotacoes.index', compact('data', 'fornecedor'));
    }

    public function create()
    {
        $fornecedores = Fornecedor::where('empresa_id', request()->empresa_id)->get();
        return view('cotacoes.create', compact('fornecedores'));
    }

    public function edit($id)
    {
        $item = Cotacao::findOrfail($id);
        $fornecedores = Fornecedor::where('empresa_id', request()->empresa_id)->get();
        return view('cotacoes.edit', compact('fornecedores', 'item'));
    }

    public function store(Request $request){
        try{
            DB::transaction(function () use ($request) {
                $referencia = Str::random(15);

                for($i=0; $i<sizeof($request->fornecedor_id); $i++){
                    $fornecedor = Fornecedor::findOrFail($request->fornecedor_id[$i]);

                    $cotacao = Cotacao::create([
                        'empresa_id' => $request->empresa_id,
                        'fornecedor_id' => $fornecedor->id,
                        'hash_link' => Str::random(30),
                        'referencia' => $referencia,
                        'observacao' => $request->observacao ?? '',
                        'estado' => $request->estado
                    ]);
                    for($j=0; $j<sizeof($request->produto_id); $j++){
                        ItemCotacao::create([
                            'cotacao_id' => $cotacao->id,
                            'quantidade' => __convert_value_bd($request->quantidade[$j]),
                            'produto_id' => $request->produto_id[$j],
                        ]);
                    }

                    $this->enviarEmailCotacao($cotacao);
                }
            });
            session()->flash("flash_success", 'Cotação criada!');
            return redirect()->route('cotacoes.index');
        } catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
            return redirect()->back();
        }
    }

    private function enviarEmailCotacao($cotacao){
        if($cotacao->fornecedor->email != ''){
            $email = $cotacao->fornecedor->email;
            $teste = Mail::send('mail.cotacao', ['cotacao' => $cotacao], function($m) use ($email){

                $nomeEmail = env('MAIL_FROM_NAME');
                $m->from(env('MAIL_USERNAME'), $nomeEmail);
                $m->subject('envio de cotação');
                $m->to($email);
            });
        }
    }

    public function update(Request $request, $id){
        try{
            DB::transaction(function () use ($request, $id) {

                $cotacao = Cotacao::findOrfail($id);
                $cotacao->fill($request->all())->save();
                $cotacao->itens()->delete();

                for($j=0; $j<sizeof($request->produto_id); $j++){
                    ItemCotacao::create([
                        'cotacao_id' => $cotacao->id,
                        'quantidade' => __convert_value_bd($request->quantidade[$j]),
                        'produto_id' => $request->produto_id[$j],
                    ]);
                }
            });
            session()->flash("flash_success", 'Cotação atualizada!');
            return redirect()->route('cotacoes.index');
        } catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
            return redirect()->back();
        }
    }

    public function show($id)
    {
        $item = Cotacao::findOrfail($id);

        $cotacaoComCompra = Cotacao::where('referencia', $item->referencia)
        ->where('nfe_id', '>', 0)
        ->first();

        return view('cotacoes.show', compact('item', 'cotacaoComCompra'));
    }

    public function destroy($id)
    {
        $item = Cotacao::findOrFail($id);
        try {
            $item->itens()->delete();
            $item->fatura()->delete();
            $item->delete();
            session()->flash("flash_success", "Cotação removida com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado.', $e->getMessage());
        }
        return redirect()->route('cotacoes.index');
    }

    public function purchase($id)
    {
        //gerar compra
        $item = Cotacao::findOrFail($id);
        return redirect()->route('compras.create', ['cotacao_id='.$item->id]);

    }

}
