<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\NaturezaOperacao;
use App\Models\Nfe;
use App\Models\Produto;
use App\Models\Transportadora;
use Dompdf\Dompdf;
use Illuminate\Http\Request;

class OrcamentoController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:orcamento_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:orcamento_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:orcamento_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:orcamento_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_id = $request->get('cliente_id');
        $estado = $request->get('estado');
        $tpNF = $request->get('tpNF');

        $data = Nfe::where('empresa_id', request()->empresa_id)->where('tpNF', 1)->where('orcamento', 1)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date,) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
            return $query->where('cliente_id', $cliente_id);
        })
        ->when($estado != "", function ($query) use ($estado) {
            return $query->where('estado', $estado);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('orcamento.index', compact('data'));
    }


    public function destroy(string $id)
    {
        $item = Nfe::findOrFail($id);
        try {

            $item->itens()->delete();
            $item->fatura()->delete();
            $item->delete();
            $descricaoLog = $item->cliente->info . " R$ " . __moeda($item->total);
            __createLog(request()->empresa_id, 'Orçamento', 'excluir', $descricaoLog);
            session()->flash("flash_success", "Orçamento removido!");
        } catch (\Exception $e) {
            __createLog(request()->empresa_id, 'Orçamento', 'erro', $e->getMessage());
            session()->flash("flash_error", 'Algo deu errado.', $e->getMessage());
        }
        return redirect()->back();
    }

    public function imprimir($id)
    {
        $item = Nfe::findOrFail($id);
        __validaObjetoEmpresa($item);

        $config = Empresa::where('id', $item->empresa_id)->first();

        $p = view('orcamento.imprimir', compact('config', 'item'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Orçamento de Venda $id.pdf", array("Attachment" => false));
    }

    public function show($id)
    {
        $data = Nfe::findOrFail($id);
        __validaObjetoEmpresa($data);

        $config = Empresa::where('id', $data->empresa_id)->first();

        return view('orcamento.show', compact('config', 'data'));
    }

    public function gerarVenda($id)
    {
        $data = Nfe::findOrFail($id);
        $data->orcamento = 0;
        $data->save();
        session()->flash("flash_success", "Orçamento transformado em venda!");
        return redirect()->route('nfe.index');

    }

    public function gerarVendaMultipla(Request $request){
        $data = [];
        $orcamentosId = [];
        $itens = [];
        for($i=0; $i<sizeof($request->orcamento_id); $i++){
            $item = Nfe::findOrFail($request->orcamento_id[$i]);
            $data[] = $item;
            $orcamentosId[] = $request->orcamento_id[$i];
            foreach($item->itens as $it){
                $indice = 0;
                for($j=0; $j<sizeof($itens); $j++){
                    if($it->produto_id == $itens[$j]->produto_id){
                        $indice = $j;
                    }
                }
                if($indice == 0){
                    $itens[] = $it;
                }else{
                    $itens[$indice]->quantidade += $it->quantidade;
                }
            }
        }

        foreach($itens as $it){
            $it->sub_total = $it->quantidade * $it->valor_unitario;
        }

        $cliente = $item->cliente;

        if(!$item->cliente){
            session()->flash("flash_error", "Cliente não cadastrado no sistema");
            return redirect()->back();
        }
        $cliente = $item->cliente;
        
        $cidades = Cidade::all();
        $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();

        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        if (sizeof($naturezas) == 0) {
            session()->flash("flash_warning", "Primeiro cadastre um natureza de operação!");
            return redirect()->route('natureza-operacao.create');
        } 
        // $produtos = Produto::where('empresa_id', request()->empresa_id)->get();
        $empresa = Empresa::findOrFail(request()->empresa_id);

        $caixa = __isCaixaAberto();
        $empresa = __objetoParaEmissao($empresa, $caixa->local_id);
        $numeroNfe = Nfe::lastNumero($empresa);

        $item->cliente_id = $cliente->id;
        $item->itens = $itens;

        return view('nfe.create', compact('item', 'cidades', 'transportadoras', 'naturezas', 'orcamentosId', 'numeroNfe',
            'caixa'));
    }

    public function edit($id)
    {
        $item = Nfe::findOrFail($id);
        __validaObjetoEmpresa($item);
        $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();
        $cidades = Cidade::all();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        $caixa = __isCaixaAberto();
        $isOrcamento = 1;
        
        return view('nfe.edit', compact('item', 'transportadoras', 'cidades', 'naturezas', 'caixa', 'isOrcamento'));
    }
}
