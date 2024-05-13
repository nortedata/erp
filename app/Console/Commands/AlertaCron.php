<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empresa;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\Notificacao;
use App\Models\ItemNfe;
use App\Models\Produto;
use App\Models\Estoque;
use App\Models\ConfigGeral;

class AlertaCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerta:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria alertas para empresas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $empresas = Empresa::where('status', 1)->get();

        foreach($empresas as $empresa){

            $config = ConfigGeral::where('empresa_id', $empresa->id)->first();
            $alertasAtivos = null;
            if($config != null){
                $alertasAtivos = json_decode($config->notificacoes);
            }
            // dd($alertasAtivos);
            if($alertasAtivos == null || in_array('Contas a receber', $alertasAtivos)){
                $contasReceber = ContaReceber::where('empresa_id', $empresa->id)
                ->where('status', 0)
                ->whereDate('data_vencimento', date('Y-m-d'))
                ->get();

                foreach($contasReceber as $conta){
                    $descricaoCurta = $conta->cliente->razao_social . " R$" . __moeda($conta->valor_integral);
                    $this->criaNotificacao('conta_recebers', $conta->id, $empresa, 'Conta a receber', $descricaoCurta, $conta);
                }
            }

            if($alertasAtivos == null || in_array('Contas a pagar', $alertasAtivos)){
                $contasPagar = ContaPagar::where('empresa_id', $empresa->id)
                ->where('status', 0)
                ->whereDate('data_vencimento', date('Y-m-d'))
                ->get();

                foreach($contasPagar as $conta){
                    $descricaoCurta = $conta->fornecedor->razao_social . " R$" . __moeda($conta->valor_integral);
                    $this->criaNotificacao('conta_pagars', $conta->id, $empresa, 'Conta a pagar', $descricaoCurta, $conta);
                }
            }

            if($alertasAtivos == null || in_array('Alerta de validade', $alertasAtivos)){
                $produtosComAlertaValidade = Produto::where('empresa_id', $empresa->id)
                ->where('alerta_validade', '>', 0)->get();
                foreach($produtosComAlertaValidade as $produto){
                    $date = date('Y-m-d', strtotime(date('Y-m-d'). "+$produto->alerta_validade days"));
                    $itens = ItemNfe::where('produto_id', $produto->id)
                    ->whereDate('data_vencimento', $date)
                    ->get();

                    foreach($itens as $i){
                        $descricaoCurta = $i->produto->nome;
                        $this->criaNotificacao('compras', $i->id, $empresa, 'Alerta de vencimento', $descricaoCurta, $i, 'media');
                    }
                }
            }

            if($alertasAtivos == null || in_array('Alerta de estoque', $alertasAtivos)){
                $produtosComEstoqueMinimo = Produto::where('empresa_id', $empresa->id)
                ->where('estoque_minimo', '>', 0)->get();
                foreach($produtosComEstoqueMinimo as $produto){
                    $estoque = Estoque::where('produto_id', $produto->id)->first();

                    if($estoque != null && $estoque->quantidade <= $produto->estoque_minimo){
                        $descricaoCurta = $produto->nome;
                        $this->criaNotificacao('estoques', $estoque->id, $empresa, 'Alerta de estoque', $descricaoCurta, $estoque, 'media');
                    }
                }
            }

        }
    }

    private function criaNotificacao($tabela, $referencia, $empresa, $titulo, $descricaoCurta, $objeto, $prioridade = 'baixa'){
        $item = Notificacao::where('empresa_id', $empresa->id)
        ->where('tabela', $tabela)
        ->where('referencia', $referencia)->first();

        if($item == null){
            $descricao = $this->getDescricao($tabela, $objeto);
            Notificacao::create([
                'empresa_id' => $empresa->id,
                'tabela' => $tabela,
                'descricao' => $descricao,
                'descricao_curta' => $descricaoCurta,
                'referencia' => $referencia,
                'status' => 1,
                'por_sistema' => 1,
                'prioridade' => $prioridade, 
                'visualizada' => 0,
                'titulo' => $titulo
            ]);
        }
    }

    private function getDescricao($tabela, $item){
        if($tabela == 'conta_recebers'){
            return view('notificacao.partials.conta_receber', compact('item'));
        }
        if($tabela == 'conta_pagars'){
            return view('notificacao.partials.conta_pagar', compact('item'));
        }
        if($tabela == 'compras'){
            return view('notificacao.partials.compras', compact('item'));
        }
        if($tabela == 'estoques'){
            return view('notificacao.partials.estoques', compact('item'));
        }
    }
}
