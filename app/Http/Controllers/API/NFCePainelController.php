<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nfce;
use App\Models\Empresa;
use App\Services\NFCeService;

class NFCePainelController extends Controller
{
    public function __construct(){
        if (!is_dir(public_path('xml_nfce'))) {
            mkdir(public_path('xml_nfce'), 0777, true);
        }
        if (!is_dir(public_path('xml_nfce_cancelada'))) {
            mkdir(public_path('xml_nfce_cancelada'), 0777, true);
        }
    }

    public function emitir(Request $request){

        $nfce = Nfce::findOrFail($request->id);

        $empresa = Empresa::findOrFail($nfce->empresa_id);

        if($empresa->arquivo == null){
            return response()->json("Certificado não encontrado para este emitente", 401);
        }

        $nfe_service = new NFCeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$nfce->ambiente,
            "razaosocial" => $empresa->nome,
            "siglaUF" => $empresa->cidade->uf,
            "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "CSC" => $empresa->csc,
            "CSCid" => $empresa->csc_id
        ], $empresa);

        $doc = $nfe_service->gerarXml($nfce);

        if(!isset($doc['erros_xml'])){
            $xml = $doc['xml'];
            $chave = $doc['chave'];

            try{
                $signed = $nfe_service->sign($xml);
                $resultado = $nfe_service->transmitir($signed, $doc['chave']);

                if ($resultado['erro'] == 0) {
                    $nfce->chave = $doc['chave'];
                    $nfce->estado = 'aprovado';

                    if($empresa->ambiente == 2){
                        $empresa->numero_ultima_nfce_homologacao = $doc['numero'];
                    }else{
                        $empresa->numero_ultima_nfce_producao = $doc['numero'];
                    }
                    $nfce->numero = $doc['numero'];
                    $nfce->recibo = $resultado['success'];
                    $nfce->data_emissao = date('Y-m-d H:i:s');
                    
                    $nfce->save();
                    $empresa->save();
                    $data = [
                        'recibo' => $resultado['success'],
                        'chave' => $nfce->chave
                    ];
                    return response()->json($data, 200);
                }else{
                    $error = $resultado['error'];
                    if(isset($error['protNFe'])){
                        $motivo = $error['protNFe']['infProt']['xMotivo'];
                        $cStat = $error['protNFe']['infProt']['cStat'];

                        $nfce->chave = $doc['chave'];
                        $nfce->estado = 'rejeitado';
                        $nfce->motivo_rejeicao = substr("[$cStat] $motivo", 0, 200);
                        $nfce->save();

                        return response()->json("[$cStat] $motivo", 403);
                    }else{
                        return response()->json($error, 403);
                    }
                }
            }catch(\Exception $e){
                return response()->json($e->getMessage(), 404);
            }

        }else{
            return response()->json($doc['erros_xml'], 401);
        }
    }

    public function cancelar(Request $request)
    {
        $nfce = Nfce::findOrFail($request->id);
        $empresa = Empresa::findOrFail($nfce->empresa_id);

        if ($nfce != null) {
            $cnpj = preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj);
            $nfe_service = new NFCeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$nfce->ambiente,
                "razaosocial" => $empresa->nome,
                "siglaUF" => $empresa->cidade->uf,
                "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
            ], $empresa);
            $doc = $nfe_service->cancelar($nfce, $request->motivo);

            if (!isset($doc['erro'])) {
                $nfce->estado = 'cancelado';
                $nfce->save();
                // return response()->json($doc, 200);
                $motivo = $doc['retEvento']['infEvento']['xMotivo'];
                $cStat = $doc['retEvento']['infEvento']['cStat'];
                if($cStat == 135){
                    return response()->json("[$cStat] $motivo", 200);
                }else{
                    return response()->json("[$cStat] $motivo", 401);
                }
            } else {
                $arr = $doc['data'];
                $cStat = $arr['retEvento']['infEvento']['cStat'];
                $motivo = $arr['retEvento']['infEvento']['xMotivo'];
                
                return response()->json("[$cStat] $motivo", $doc['status']);
            }
        } else {
            return response()->json('Consulta não encontrada', 404);
        }
    }

    public function consultar(Request $request)
    {
        $nfce = Nfce::findOrFail($request->id);
        $empresa = Empresa::findOrFail($nfce->empresa_id);
        if ($nfce != null) {
            $cnpj = preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj);
            $nfe_service = new NFCeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$nfce->ambiente,
                "razaosocial" => $empresa->nome,
                "siglaUF" => $empresa->cidade->uf,
                "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
            ], $empresa);
            $consulta = $nfe_service->consultar($nfce);
            if (!isset($nconsultafe['erro'])) {
                $motivo = $consulta['protNFe']['infProt']['xMotivo'];
                $cStat = $consulta['protNFe']['infProt']['cStat'];
                if($cStat == 100){
                    return response()->json("[$cStat] $motivo", 200);
                }else{
                    return response()->json("[$cStat] $motivo", 401);
                }
            }else{
                return response()->json($consulta['data'], $consulta['status']);
            }
        } else {
            return response()->json('Consulta não encontrada', 404);
        }
    }

    public function consultaStatusSefaz(Request $request){
        $empresa = Empresa::findOrFail($request->empresa_id);

        $nfce_service = new NFCeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$empresa->ambiente,
            "razaosocial" => $empresa->nome,
            "siglaUF" => $empresa->cidade->uf,
            "cnpj" => preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj),
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
        ], $empresa);
        $consulta = $nfce_service->consultaStatus((int)$empresa->ambiente, $empresa->cidade->uf);
        return response()->json($consulta, 200);
    }

}
