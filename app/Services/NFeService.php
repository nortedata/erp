<?php
namespace App\Services;

error_reporting(E_ALL);
ini_set('display_errors', 'On');
use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;
use NFePHP\NFe\Make;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use App\Models\Nfe;
use App\Models\Difal;
use App\Models\Empresa;
use App\Models\ConfiguracaoSuper;
use App\Models\Ibpt;

class NFeService{

	private $config; 
	private $tools;
	protected $empresa_id = null;

	protected $timeout = 8;

	public function __construct($config, $empresa){
		
		$this->empresa_id = $empresa->id;

		$this->config = $config;
		$this->tools = new Tools(json_encode($config), Certificate::readPfx($empresa->arquivo, $empresa->senha));
		$this->tools->model(55);

		$config = ConfiguracaoSuper::first();
		if($config){
			if($config->timeout_nfe){
				$this->timeout = $config->timeout_nfe;
			}
		}
	}

	public function gerarXml($item){

		$nfe = new Make();
		$stdInNFe = new \stdClass();
		$stdInNFe->versao = '4.00';
		$stdInNFe->Id = null;
		$stdInNFe->pk_nItem = '';

		$infNFe = $nfe->taginfNFe($stdInNFe);
		$emitente = $item->empresa;
		$cliente = $item->cliente;

		$stdIde = new \stdClass();
		$stdIde->cUF = Empresa::getCodUF($emitente->cidade->uf); // codigo uf emitente
		$stdIde->cNF = rand(11111, 99999);
		// $stdIde->natOp = $venda->natureza->natureza;
		$stdIde->natOp = $item->natureza->descricao;

		$contact = $item->cliente;
		if($contact == null){
			$contact = $item->fornecedor;
		}
		$stdIde->mod = 55;
		$stdIde->serie = $item->numero_serie;
		// $stdIde->nNF = $item->lastNumero(); // numero sequencial da nfe

		if($emitente->ambiente == 2){
			$nNF = $emitente->numero_ultima_nfe_homologacao;
		}else{
			$nNF = $emitente->numero_ultima_nfe_producao;
		}

		$stdIde->nNF = $item->numero; 
		$stdIde->dhEmi = date("Y-m-d\TH:i:sP");
		$stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
		$stdIde->tpNF = $item->tpNF;
		$stdIde->idDest = $emitente->cidade->uf == $contact->cidade->uf ? 1 : 2;
		$stdIde->cMunFG = $emitente->cidade->codigo;
		$stdIde->tpImp = 1;
		$stdIde->tpEmis = 1;
		$stdIde->cDV = 0;
		$stdIde->tpAmb = (int)$emitente->ambiente;
		$stdIde->finNFe = $item->finNFe;
		$stdIde->indFinal = $contact->consumidor_final;
		$stdIde->indPres = 1;
		$stdIde->procEmi = '0';
		$stdIde->verProc = '2.0';
		$tagide = $nfe->tagide($stdIde); //fim da tagide

		// inicia tag do emitente
		$stdEmit = new \stdClass();
		$stdEmit->xNome = $emitente->nome;
		$stdEmit->xFant = $emitente->nome_fantasia;
		$stdEmit->CRT = $emitente->tributacao == 'Regime Normal' ? 3 : 1;
		$stdEmit->IE = preg_replace('/[^0-9]/', '', $emitente->ie);

		$cpf_cnpj = preg_replace('/[^0-9]/', '', $emitente->cpf_cnpj);
		if (strlen($cpf_cnpj) == 14) {
			$stdEmit->CNPJ = $cpf_cnpj;
		}else{
			$stdEmit->CPF = $cpf_cnpj;
		}
		$emit = $nfe->tagemit($stdEmit);

		$stdEnderEmit = new \stdClass();
		$stdEnderEmit->xLgr = $emitente->rua;
		$stdEnderEmit->nro = $emitente->numero;
		$stdEnderEmit->xCpl = $emitente->complemento;
		$stdEnderEmit->xBairro = $emitente->bairro;
		$stdEnderEmit->cMun = $emitente->cidade->codigo;
		$stdEnderEmit->xMun = $emitente->cidade->nome;
		$stdEnderEmit->UF = $emitente->cidade->uf;
		$stdEnderEmit->CEP = preg_replace('/[^0-9]/', '', $emitente->cep);
		$stdEnderEmit->cPais = '1058';
		$stdEnderEmit->xPais = 'BRASIL';
		$enderEmit = $nfe->tagenderEmit($stdEnderEmit); // fim tag do emitente

		// inicia tag do destinatario
		$stdDest = new \stdClass();
		$stdDest->xNome = $contact->razao_social;
		if ($contact->contribuinte == 1) {
			if ($contact->ie == '') {
				$stdDest->indIEDest = "2";
			} else {
				$stdDest->indIEDest = "1";
			}
		} else {
			$stdDest->indIEDest = "9";
		}

		$cpf_cnpj = preg_replace('/[^0-9]/', '', $contact->cpf_cnpj);

		if (strlen($cpf_cnpj) == 14) {
			$stdDest->CNPJ = $cpf_cnpj;
			$ie = preg_replace('/[^0-9]/', '', $contact->ie);
			$stdDest->IE = $ie;
		} else {
			$stdDest->CPF = $contact->cpf_cnpj;
		}

		$dest = $nfe->tagdest($stdDest);

		$stdEnderDest = new \stdClass();
		$stdEnderDest->xLgr = $contact->rua;
		$stdEnderDest->nro = $contact->numero;
		$stdEnderDest->xCpl = $contact->complemento;
		$stdEnderDest->xBairro = $contact->bairro;
		$stdEnderDest->cMun = $contact->cidade->codigo;
		$stdEnderDest->xMun = $contact->cidade->nome;
		$stdEnderDest->UF = $contact->cidade->uf;
		$stdEnderDest->fone = preg_replace('/[^0-9]/', '', $contact->telefone);
		$stdEnderDest->CEP = preg_replace('/[^0-9]/', '', $contact->cep);
		$stdEnderDest->cPais = '1058';
		$stdEnderDest->xPais = 'BRASIL';

		$enderDest = $nfe->tagenderDest($stdEnderDest);

		//fim tag destinatario endereço

		if(strlen($item->referencia) >= 44){
			$std = new \stdClass();
			$std->refNFe = $item->referencia;
			$nfe->tagrefNFe($std);
		}

		$somaProdutos = 0;
		$somaICMS = 0;
		$somaFrete = 0;
		$somaDesconto = 0;
		$somaIpi = 0;
		$totalItens = sizeof($item->itens);
		$VBC = 0;
		$obsIbpt = "";
		$somaFederal = 0;
		$somaEstadual = 0;
		$somaMunicipal = 0;

		foreach ($item->itens as $itemCont => $i) {
			$itemCont++;

			$stdProd = new \stdClass(); // tag produto inicio
			$stdProd->item = $itemCont;
			$stdProd->cEAN = $i->produto->codigo_barras ? $i->produto->codigo_barras : 'SEM GTIN';
			$stdProd->cEANTrib = $i->produto->codigo_barras ? $i->produto->codigo_barras : 'SEM GTIN';
			$stdProd->cProd = $i->produto->id;
			$stdProd->xProd = $i->descricao();
			$stdProd->NCM = preg_replace('/[^0-9]/', '', $i->ncm);

			$ibpt = Ibpt::getItemIbpt($emitente->cidade->uf, preg_replace('/[^0-9]/', '', $i->ncm));

			$stdProd->CFOP = $i->cfop;
			$stdProd->uCom = $i->produto->unidade;
			$stdProd->qCom = $i->quantidade;
			$stdProd->vUnCom = $this->format($i->valor_unitario);
			$stdProd->vProd = $this->format(($i->quantidade * $i->valor_unitario));
			$stdProd->uTrib = $i->produto->unidade;
			$stdProd->qTrib = $i->quantidade;
			$stdProd->vUnTrib = $this->format($i->valor_unitario);
			$stdProd->indTot = 1;

			if($i->codigo_beneficio_fiscal){
				$stdProd->cBenef = $i->codigo_beneficio_fiscal;
			}

			if($item->valor_frete > 0){

				if($itemCont < $totalItens){
					$somaFrete += $vFt = 
					$this->format($item->valor_frete/$totalItens, 2);
					$stdProd->vFrete = $this->format($vFt);
				}else{
					$stdProd->vFrete = $this->format(($item->valor_frete-$somaFrete), 2);
				}
			}

			if($item->desconto > 0.01){

				if($itemCont < $totalItens){
					$totalVenda = $item->total;

					$media = (((($stdProd->vProd - $totalVenda)/$totalVenda))*100);
					$media = 100 - ($media * -1);

					$tempDesc = ($item->desconto*$media)/100;

					if($tempDesc > 0.01){
						$somaDesconto += $tempDesc;
						$stdProd->vDesc = $this->format($tempDesc);
					}else{
						$somaDesconto = $venda->desconto;
						$stdProd->vDesc = $this->format($somaDesconto);
					}

				}else{
					if(($item->desconto - $somaDesconto) > 0.01){
						$stdProd->vDesc = $this->format($item->desconto - $somaDesconto, 2);
					}
				}
			}

			$prod = $nfe->tagprod($stdProd); // fim tag de produtos

			$stdImposto = new \stdClass();
			$stdImposto->item = $itemCont;

			if($ibpt != null){

				$vProd = $stdProd->vProd;

				if($i->produto->origem == 1 || $i->produto->origem == 2){
					$federal = $this->format(($vProd*($ibpt->importado_federal/100)), 2);

				}else{
					$federal = $this->format(($vProd*($ibpt->nacional_federal/100)), 2);
				}
				$somaFederal += $federal;

				$estadual = $this->format(($vProd*($ibpt->estadual/100)), 2);
				$somaEstadual += $estadual;

				$municipal = $this->format(($vProd*($ibpt->municipal/100)), 2);
				$somaMunicipal += $municipal;

				$soma = $federal + $estadual + $municipal;
				$stdImposto->vTotTrib = $soma;

				$obsIbpt = " FONTE: " . $ibpt->versao ?? '';
				$obsIbpt .= " | ";
			}
			$imposto = $nfe->tagimposto($stdImposto); // tag imposto

			//frete

			if ($stdEmit->CRT == 1) {

				$stdICMS = new \stdClass();

				$stdICMS->item = $itemCont; 
				$stdICMS->orig = 0;
				$stdICMS->CSOSN = $i->cst_csosn;

				if(isset($i['vBCSTRet'])) $stdICMS->vBCSTRet = $i['vBCSTRet'];
				if(isset($i['pST'])) $stdICMS->pST = $i['pST'];
				if(isset($i['vICMSSTRet'])) $stdICMS->vICMSSTRet = $i['vICMSSTRet'];
				if(isset($i['vICMSSubstituto'])) $stdICMS->vICMSSubstituto = $i['vICMSSubstituto'];

				if($i->perc_red_bc > 0){
					$stdICMS->pRedBC = $this->format($i->perc_red_bc);
					
					$tempB = 100 - $i->perc_red_bc;
					$v = $stdProd->vProd * ($tempB/100);
					$v += $stdProd->vFrete;
					$VBC += $stdICMS->vBC = number_format($v,2,'.','');
					$stdICMS->pICMS = $this->format($i->perc_icms);
					$somaICMS += $stdICMS->vICMS = ($stdProd->vProd * ($tempB/100)) * ($stdICMS->pICMS/100);
					$stdICMS->pRedBC = $this->format($i->produto->pRedBC);
				}
				
				$stdICMS->pCredSN = $this->format($i->perc_icms);
				$stdICMS->vCredICMSSN = $this->format($i->perc_icms);
				$ICMS = $nfe->tagICMSSN($stdICMS);

				$somaICMS = 0;
			} else if ($stdEmit->CRT == 3) {
				if($stdIde->idDest == 2 && $stdIde->indFinal == 1){
					$difal = Difal::where('cfop', $stdProd->CFOP)->first();
					if($difal){
						$i->perc_icms = $difal->pICMSInter;
					}
				}

				$somaProdutos += $stdProd->vProd;

				$stdICMS = new \stdClass();
				$stdICMS->item = $itemCont; 
				$stdICMS->orig = 0;
				$stdICMS->CST = $i->cst_csosn;
				$stdICMS->modBC = 0;
				$stdICMS->vBC = $this->format($i->valor_unitario * $i->quantidade);
				$stdICMS->pICMS = $this->format($i->perc_icms);
				$stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS/100);

				if($i->perc_red_bc > 0){
					$stdICMS->pRedBC = $this->format($i->perc_red_bc);

					$tempB = 100 - $i->perc_red_bc;
					$v = $stdProd->vProd * ($tempB/100);
					$v += $stdProd->vFrete;
					$VBC += $stdICMS->vBC = number_format($v,2,'.','');
					$stdICMS->pICMS = $this->format($i->perc_icms);
					$somaICMS += $stdICMS->vICMS = ($stdProd->vProd * ($tempB/100)) * ($stdICMS->pICMS/100);
					$stdICMS->pRedBC = $this->format($i->produto->pRedBC);
				}else{
					$somaICMS += (($i->valor_unitario * $i->quantidade) 
						* ($stdICMS->pICMS/100));
				}

				if(isset($i['vBCSTRet'])) $stdICMS->vBCSTRet = $i['vBCSTRet'];
				if(isset($i['pST'])) $stdICMS->vBCSTRet = $i['pST'];
				if(isset($i['vICMSSTRet'])) $stdICMS->vBCSTRet = $i['vICMSSTRet'];
				if(isset($i['vICMSSubstituto'])) $stdICMS->vBCSTRet = $i['vICMSSubstituto'];

				
				if($i->cst_csosn == '60'){
					$ICMS = $nfe->tagICMSST($stdICMS);
				}else{
					$ICMS = $nfe->tagICMS($stdICMS);
				}
			} // fim tag icms

			//PIS

			$vbcPis = $stdICMS->vBC;
			if($emitente->exclusao_icms_pis_cofins){
				$vbcPis -= $stdICMS->vICMS;
			}
			$stdPIS = new \stdClass();
			$stdPIS->item = $itemCont;
			$stdPIS->CST = $i->cst_pis;
			$stdPIS->vBC = $this->format($i->perc_pis) > 0 ? $vbcPis : 0.00;
			$stdPIS->pPIS = $this->format($i->perc_pis);
			$stdPIS->vPIS = $this->format($vbcPis * ($i->perc_pis / 100));
			$PIS = $nfe->tagPIS($stdPIS);

			//COFINS
			$vbcCofins = $stdICMS->vBC;
			if($emitente->exclusao_icms_pis_cofins){
				$vbcCofins -= $stdICMS->vICMS;
			}
			$stdCOFINS = new \stdClass();
			$stdCOFINS->item = $itemCont;
			$stdCOFINS->CST = $i->cst_cofins;
			$stdCOFINS->vBC = $this->format($i->perc_cofins) > 0 ? $vbcCofins : 0.00;
			$stdCOFINS->pCOFINS = $this->format($i->perc_cofins);
			$stdCOFINS->vCOFINS = $this->format($vbcCofins * ($i->perc_cofins / 100));
			$COFINS = $nfe->tagCOFINS($stdCOFINS);


			$std = new \stdClass(); //IPI
			$std->item = $itemCont;
			$std->clEnq = null;
			$std->CNPJProd = null;
			$std->cSelo = null;
			$std->qSelo = null;
			$std->cEnq = 999;
			$std->CST = $i->cst_ipi;
			$std->vBC = $this->format($i->perc_ipi) > 0 ? $stdProd->vProd : 0.00;
			$std->pIPI = $this->format($i->perc_ipi);
			$somaIpi += $std->vIPI = $stdProd->vProd * $this->format(($i->perc_ipi / 100));
			$std->qUnid = null;
			$std->vUnid = null;

			$IPI = $nfe->tagIPI($std);

			if(strlen($i->produto->codigo_anp) > 2){
				$stdComb = new \stdClass();
				$stdComb->item = $itemCont; 
				$stdComb->cProdANP = $i->produto->codigo_anp;
				$stdComb->descANP = $i->produto->getDescricaoAnp();
				if($i->produto->perc_glp > 0){
					$stdComb->pGLP = $this->format($i->produto->perc_glp);
				}

				if($i->produto->perc_gnn > 0){
					$stdComb->pGNn = $this->format($i->produto->perc_gnn);
				}

				if($i->produto->perc_gni > 0){
					$stdComb->pGNi = $this->format($i->produto->perc_gni);
				}

				$stdComb->vPart = $this->format($i->produto->valor_partida);
				$stdComb->UFCons = $item->cliente->cidade->uf;

				if($i->produto->pBio > 0){
					$stdComb->pBio = $i->produto->pBio;
				}
				$nfe->tagcomb($stdComb);
			}

			if($stdIde->indFinal == 0 && strlen($i->produto->codigo_anp) > 2){
				$stdOrigComb = new \stdClass();

				$stdOrigComb->item = $itemCont; 
				$stdOrigComb->indImport = $i->produto->indImport;
				$stdOrigComb->cUFOrig = $i->produto->cUFOrig;
				$stdOrigComb->pOrig = $i->produto->pOrig;
				$nfe->tagorigComb($stdOrigComb);
			}

			$cest = $i->cest;
			$cest = str_replace(".", "", $cest);
			$stdProd->CEST = $cest;
			if(strlen($cest) > 0){
				$std = new \stdClass();
				$std->item = $itemCont; 
				$std->CEST = $cest;
				$nfe->tagCEST($std);
			}

			if($stdIde->idDest == 2 && $stdIde->indFinal == 1 && $stdEmit->CRT == 3){
				$difal = Difal::where('cfop', $stdProd->CFOP)->first();

				if($difal){

					$std = new \stdClass();
					$std->item = $itemCont; 
					$std->vBCUFDest = $stdICMS->vBC;
					$std->vBCFCPUFDest = $stdICMS->vBC;
					$std->pFCPUFDest = $this->format($difal->pFCPUFDest);
					$std->pICMSUFDest = $this->format($difal->pICMSUFDest);

					$std->pICMSInter = $this->format($difal->pICMSInter);
					$std->pICMSInterPart = $this->format($difal->pICMSInterPart);
					$std->vFCPUFDest = $this->format($std->vBCUFDest * ($std->pFCPUFDest/100));

					$vICMSUFDest = $std->vBCFCPUFDest * ($std->pICMSInter/100);
					$vICMSUFDestAux = $stdICMS->vBC * ($std->pICMSUFDest/100);
					$std->vICMSUFDest = $this->format($vICMSUFDestAux-$vICMSUFDest);
					$std->vICMSUFRemet = $this->format($vICMSUFDestAux-$vICMSUFDest) - $std->vICMSUFDest;

					$nfe->tagICMSUFDest($std);
				}
			}
		}

		$stdICMSTot = new \stdClass();
		$stdICMSTot->vBC = $this->format($VBC);
		$stdICMSTot->vICMS = $this->format($somaICMS);
		$stdICMSTot->vICMSDeson = 0.00;
		$stdICMSTot->vBCST = 0.00;
		$stdICMSTot->vST = 0.00;
		$stdICMSTot->vProd = 0;
		$stdICMSTot->vFrete = $item->valor_frete;
		$stdICMSTot->vSeg = 0.00;
		$stdICMSTot->vDesc = $this->format($item->desconto);
		$stdICMSTot->vII = 0.00;
		$stdICMSTot->vIPI = 0.00;
		$stdICMSTot->vPIS = 0.00;
		$stdICMSTot->vCOFINS = 0.00;
		$stdICMSTot->vOutro = 0.00;
		$stdICMSTot->vNF = $this->format($item->total);
		$stdICMSTot->vTotTrib = 0.00;
		$ICMSTot = $nfe->tagICMSTot($stdICMSTot);

		$stdTransp = new \stdClass();
		$stdTransp->modFrete = $item->tipo != null ? $item->tipo : 9;

		$transp = $nfe->tagtransp($stdTransp);

		if ($item->qtd_volumes && $item->numero_volumes && $item->peso_liquido) {
			$stdVol = new \stdClass();
			$stdVol->item = 1;
			$stdVol->qVol = $item->qtd_volumes;
			$stdVol->esp = $item->especie;

			$stdVol->nVol = $item->numeracao_volumes;
			$stdVol->pesoL = $item->peso_liquido;
			$stdVol->pesoB = $item->peso_bruto;
			$vol = $nfe->tagvol($stdVol);
		}

		if($item->aut_xml != ''){
			$std = new \stdClass();
			$cnpj = preg_replace('/[^0-9]/', '', $item->aut_xml);
			$std->CNPJ = $cnpj;
			$aut = $nfe->tagautXML($std);
		}

		$respTec = ConfiguracaoSuper::first();
		if($respTec != null){
			$stdResp = new \stdClass();
			$stdResp->CNPJ = preg_replace('/[^0-9]/', '', $respTec->cpf_cnpj);
			$stdResp->xContato = $respTec->name;
			$stdResp->email = $respTec->email;
			$stdResp->fone = preg_replace('/[^0-9]/', '', $respTec->telefone);
			$nfe->taginfRespTec($stdResp);
		}

		//Fatura
		$stdFat = new \stdClass();
		$stdFat->nFat = $stdIde->nNF;
		$stdFat->vOrig = $this->format($item->itens->sum('sub_total') + $item->valor_frete);
		$stdFat->vDesc = $this->format($item->desconto);
		$stdFat->vLiq = $this->format($item->total);

		$fatura = $nfe->tagfat($stdFat);

		$contFatura = 1;
		foreach ($item->fatura as $ft) {
			$stdDup = new \stdClass();
			$stdDup->nDup = "00" . $contFatura;
			$stdDup->dVenc = substr($ft->data_vencimento, 0, 10);
			$stdDup->vDup = $this->format($ft->valor);

			$nfe->tagdup($stdDup);
			$contFatura++;
		}

		$stdPag = new \stdClass();
		$pag = $nfe->tagpag($stdPag);

		if(sizeof($item->fatura) > 0){
			foreach ($item->fatura as $ft) {

				$stdDetPag = new \stdClass();
				$stdDetPag->tPag = $ft->tipo_pagamento;
				$stdDetPag->vPag = $this->format($ft->valor);
				$stdDetPag->indPag = 1;
				$detPag = $nfe->tagdetPag($stdDetPag);
			}
		}else{
			$stdDetPag = new \stdClass();
			$stdDetPag->tPag = 90;
			$stdDetPag->vPag = 0;
			$stdDetPag->indPag = 1;
			$detPag = $nfe->tagdetPag($stdDetPag);
		}

		$stdInfoAdic = new \stdClass();

		$obs = $item->observacao;

		if($somaEstadual > 0 || $somaFederal > 0 || $somaMunicipal > 0){
			$obs .= " Trib. aprox. ";
			if($somaFederal > 0){
				$obs .= "R$ " . number_format($somaFederal, 2, ',', '.') ." Federal"; 
			}
			if($somaEstadual > 0){
				$obs .= ", R$ ".number_format($somaEstadual, 2, ',', '.')." Estadual"; 
			}
			if($somaMunicipal > 0){
				$obs .= ", R$ ".number_format($somaMunicipal, 2, ',', '.')." Municipal"; 
			}
			// $ibpt = IBPT::where('uf', $config->UF)->first();

			$obs .= $obsIbpt;
		}

		$stdInfoAdic->infCpl = $obs;
		$infoAdic = $nfe->taginfAdic($stdInfoAdic);

		if($emitente->aut_xml != null){
			$std = new \stdClass();
			$std->CNPJ = preg_replace('/[^0-9]/', '', $emitente->aut_xml);
			$aut = $nfe->tagautXML($std);
		}

		try{
			$nfe->montaNFe();
			$arr = [
				'chave' => $nfe->getChave(),
				'xml' => $nfe->getXML(),
				'numero' => $stdIde->nNF
			];
			return $arr;
		}catch(\Exception $e){
			return [
				'erros_xml' => $nfe->getErrors()
			];
		}

	}

	public function format($number, $dec = 2)
	{
		return number_format((float) $number, $dec, ".", "");
	}

	public function sign($xml){
		return $this->tools->signNFe($xml);
	}

	public function transmitir($signXml, $chave){
		try{
			$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
			$resp = $this->tools->sefazEnviaLote([$signXml], $idLote);

			$st = new Standardize();
			$std = $st->toStd($resp);
			sleep($this->timeout);
			if ($std->cStat != 103) {

				// return "Erro: [$std->cStat] - $std->xMotivo";
				return [
					'erro' => 1,
					'error' => "[$std->cStat] - $std->xMotivo"
				];
			}
			$recibo = $std->infRec->nRec; 
			
			$protocolo = $this->tools->sefazConsultaRecibo($recibo);
			sleep(2);
			try {
				$xml = Complements::toAuthorize($signXml, $protocolo);
				file_put_contents(public_path('xml_nfe/').$chave.'.xml',$xml);
				return [
					'erro' => 0,
					'success' => $recibo
				];
				// $this->printDanfe($xml);
			} catch (\Exception $e) {
				return [
					'erro' => 1,
					'error' => $st->toArray($protocolo)
				];
			}

		} catch(\Exception $e){
			return [
				'erro' => 1,
				'error' => $e->getMessage()
			];
		}
	}

	public function consultar($nfe){
		try {
			
			$this->tools->model('55');

			$chave = $nfe->chave;
			$response = $this->tools->sefazConsultaChave($chave);

			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();

			return $arr;

		} catch (\Exception $e) {
			return ['erro' => true, 'data' => $e->getMessage(), 'status' => 402];	
		}
	}

	public function correcao($nfe, $correcao){
		try {

			$chave = $nfe->chave;
			$xCorrecao = $correcao;
			$nSeqEvento = $nfe->sequencia_cce+1;
			$response = $this->tools->sefazCCe($chave, $xCorrecao, $nSeqEvento);
			sleep(2);

			$stdCl = new Standardize($response);

			$std = $stdCl->toStd();

			$arr = $stdCl->toArray();

			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
        //TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				if ($cStat == '135' || $cStat == '136') {
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents(public_path('xml_nfe_correcao/').$chave.'.xml', $xml);

					$nfe->sequencia_cce = $nfe->sequencia_cce + 1;
					$nfe->save();
					return $arr;

				} else {
            //houve alguma falha no evento 
					return ['erro' => true, 'data' => $arr, 'status' => 402];
				}
			}    
		} catch (\Exception $e) {
			return ['erro' => true, 'data' => $e->getMessage(), 'status' => 404];
		}
	}

	public function cancelar($nfe, $motivo){
		try {
			
			$chave = $nfe->chave;
			$response = $this->tools->sefazConsultaChave($chave);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			sleep(2);
			if(!isset($arr['protNFe'])){
				return ['erro' => true, 'data' => $arr, 'status' => 402];

			}
			$nProt = $arr['protNFe']['infProt']['nProt'];

			$response = $this->tools->sefazCancela($chave, $motivo, $nProt);
			sleep(2);
			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
        //TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				if ($cStat == '101' || $cStat == '135' || $cStat == '155' ) {
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents(public_path('xml_nfe_cancelada/').$chave.'.xml',$xml);

					return $arr;
				} else {
					
					return ['erro' => true, 'data' => $arr, 'status' => 402];	
				}
			}    
		} catch (\Exception $e) {
			// echo $e->getMessage();
			return ['erro' => true, 'data' => $e->getMessage(), 'status' => 402];
    //TRATAR
		}
	}


	public function inutilizar($inutil){
		try{

			$this->tools->model($inutil->modelo);

			$nSerie = $inutil->numero_serie;
			$nIni = $inutil->numero_inicial;
			$nFin = $inutil->numero_final;
			$xJust = $inutil->justificativa;
			$response = $this->tools->sefazInutiliza($nSerie, $nIni, $nFin, $xJust);

			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			return $arr;

		} catch (\Exception $e) {
			return ["erro" => true, "data" => $e->getMessage()];
		}
	}

	public function consultaStatus($tpAmb, $uf){
		try{
			$response = $this->tools->sefazStatus($uf, $tpAmb);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			return $arr;
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

}