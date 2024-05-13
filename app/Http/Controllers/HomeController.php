<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nfe;
use App\Models\Nfce;
use App\Models\Cte;
use App\Models\Empresa;
use App\Models\Mdfe;
use App\Models\PlanoEmpresa;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    protected $empresa_id = 1;

    public function __construct()
    {
        $this->middleware('validaCashBack');
    }

    public function homeContador(){

    }

    public function index()
    {
        
        $totalEmitidoMes = 0;
        $plano = PlanoEmpresa::where('empresa_id', request()->empresa_id)
        ->orderBy('data_expiracao', 'desc')
        ->first();

        $msgPlano = "";
        if($plano == null){
            $msgPlano = "Empresa sem plano atribuído!";
        }

        if($plano != null){
            if(date('Y-m-d') > $plano->data_expiracao){
                $msgPlano = "Plano expirado!";
            }
        }

        if(__isMaster()){
            return redirect()->route('empresas.index');
        }

        $totalNfe = Nfe::where('empresa_id', request()->empresa_id)
        ->where(function($q) {
            $q->where('estado', 'aprovado')->orWhere('estado', 'cancelado');
        })
        ->whereMonth('created_at', date('m'))
        ->sum('total');

        $totalNfce = Nfce::where('empresa_id', request()->empresa_id)
        ->where(function($q) {
            $q->where('estado', 'aprovado')->orWhere('estado', 'cancelado');
        })
        ->whereMonth('created_at', date('m'))
        ->sum('total');

        $totalEmitidoMes = $totalNfce + $totalNfe;

        $totalNfeCount = Nfe::where('empresa_id', request()->empresa_id)
        ->where(function($q) {
            $q->where('estado', 'aprovado')->orWhere('estado', 'cancelado');
        })
        ->whereMonth('created_at', date('m'))
        ->count('id');

        $totalNfceCount = Nfce::where('empresa_id', request()->empresa_id)
        ->where(function($q) {
            $q->where('estado', 'aprovado')->orWhere('estado', 'cancelado');
        })
        ->whereMonth('created_at', date('m'))
        ->count('id');

        $totalCteCount = Cte::where('empresa_id', request()->empresa_id)
        ->where(function($q) {
            $q->where('estado', 'aprovado')->orWhere('estado', 'cancelado');
        })
        ->whereMonth('created_at', date('m'))
        ->count('id');

        $totalMdfeCount = Mdfe::where('empresa_id', request()->empresa_id)
        ->where(function($q) {
            $q->where('estado_emissao', 'aprovado')->orWhere('estado_emissao', 'cancelado');
        })
        ->whereMonth('created_at', date('m'))
        ->count('id');

        $empresa = Empresa::find(request()->empresa_id);
        if($empresa == null){
            return redirect()->route('config.index');
        }

        return view('home', 
            compact('empresa', 'totalEmitidoMes', 'totalNfeCount', 'totalNfceCount', 'msgPlano', 'totalCteCount', 'totalMdfeCount'));
    }

    public function nfe()
    {
        $empresas = Empresa::orderBy('nome', 'asc')->get();
        $data = NFe::orderBy("id", "desc")
        ->paginate(30);
        return view('nfe.all', compact('data', 'empresas'));
    }

    public function nfce()
    {
        $data = Nfce::orderBy("id", "desc")
        ->paginate(30);
        return view('nfce.all', compact('data'));
    }
}
