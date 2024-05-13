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
use Svg\Tag\Rect;

class OrcamentoController extends Controller
{
    protected $util;

    /**
     * Display a listing of the resource.
     */
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
            session()->flash("flash_success", "Orçamento removido!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado.', $e->getMessage());
        }
        return redirect()->route('orcamentos.index');
    }

    public function imprimir($id)
    {
        $item = Nfe::findOrFail($id);

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
}
