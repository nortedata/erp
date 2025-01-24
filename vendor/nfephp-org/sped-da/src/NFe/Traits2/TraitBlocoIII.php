<?php

namespace NFePHP\DA\NFe\Traits2;

/**
 * Bloco itens da NFe
 */
trait TraitBlocoIII
{
    protected function blocoIII($y, $venda)
    {

        if ($this->flagResume) {
            return $y;
        }
        $matrix = [0.12, $this->descPercent, 0.08, 0.09, 0.156, 0.156];
        //$aFont = ['font'=> $this->fontePadrao, 'size' => 7, 'style' => ''];
        //$this->pdf->textBox($this->margem, $y, $this->wPrint, $this->bloco3H, '', $aFont, 'T', 'C', true, '', false);
        $fsize = 9;
        if ($this->paperwidth < 70) {
            $fsize = 5;
        }
        $aFont = ['font' => 'arial', 'size' => 9, 'style' => 'B'];

        $texto = "Código";
        $x = $this->margem;
        $this->pdf->textBox($x, $y, ($this->wPrint * $matrix[0]), 3, $texto, $aFont, 'T', 'L', false, '', true);
        
        $texto = "Descrição";
        $x1 = $x + ($this->wPrint * $matrix[0]);
        $this->pdf->textBox($x1, $y, ($this->wPrint * $matrix[1]), 3, $texto, $aFont, 'T', 'L', false, '', true);
        
        $texto = "Qtde";
        $x2 = $x1 + ($this->wPrint * $matrix[1]);
        $this->pdf->textBox($x2, $y, ($this->wPrint * $matrix[2]), 3, $texto, $aFont, 'T', 'C', false, '', true);
        
        // $texto = "UN";
        $x3 = $x2;
        // $this->pdf->textBox($x3, $y, ($this->wPrint * $matrix[3]), 3, $texto, $aFont, 'T', 'C', false, '', true);
        
        $texto = "Vl Unit";
        $x4 = $x3 + ($this->wPrint * $matrix[3]);
        $this->pdf->textBox($x4, $y, ($this->wPrint * $matrix[4]), 3, $texto, $aFont, 'T', 'C', false, '', true);
        
        $texto = "Vl Total";
        $x5 = $x4 + ($this->wPrint * $matrix[4]);
        $y1 = $this->pdf->textBox($x5, $y, ($this->wPrint * $matrix[5]), 3, $texto, $aFont, 'T', 'R', false, '', true);
        
        $y2 = $y + $y1;


        foreach ($this->venda->itens as $item) {
            $it = (object) $item;


            $this->pdf->textBox(
                $x,
                $y2,
                ($this->wPrint * $matrix[0]),
                $it->height,
                $item->produto->id,
                $aFont,
                'T',
                'L',
                false,
                '',
                true
            );
            $this->pdf->textBox(
                $x1,
                $y2,
                ($this->wPrint * $matrix[1]),
                $it->height,
                $item->descricao(),
                $aFont,
                'T',
                'L',
                false,
                '',
                false
            );
            $this->pdf->textBox(
                $x2,
                $y2,
                ($this->wPrint * $matrix[2]),
                $it->height,
                number_format($item->quantidade,2),
                $aFont,
                'T',
                'R',
                false,
                '',
                true
            );
            
            if($this->isPreVenda){
                $item->valor_unitario = $item->valor;
            }
            $this->pdf->textBox(
                $x4,
                $y2,
                ($this->wPrint * $matrix[4]-1),
                $it->height,
                number_format($item->valor_unitario, 2, ',', '.'),
                $aFont,
                'T',
                'R',
                false,
                '',
                true
            );
            $this->pdf->textBox(
                $x5,
                $y2,
                ($this->wPrint * $matrix[5]),
                $it->height,
                number_format($item->valor_unitario*$item->quantidade, 2, ',', '.'),
                $aFont,
                'T',
                'R',
                false,
                '',
                true
            );
            $y2 += $it->height;
            $y2 += 3;
            $lenDescription = strlen($item->produto->nome);

            if($lenDescription > 12 && $lenDescription < 20){
                $y2+=5;
            }else if($lenDescription > 20 && $lenDescription < 40){
                $y2+=8;
            }else if($lenDescription > 40 && $lenDescription < 60){
                $y2+=10;
            }else{
                $y2+=12;
            }
            
        }

        if($this->troca != null){
            $texto = "Itens alterados";
            $aFont2 = ['font' => 'arial', 'size' => 12, 'style' => 'B'];
            $this->pdf->textBox($x, $y2, 80, 3, $texto, $aFont2, 'T', 'C', false, '', true);

            foreach ($this->troca->itens as $item) {
                $it = (object) $item;


                $this->pdf->textBox(
                    $x,
                    $y2,
                    ($this->wPrint * $matrix[0]),
                    $it->height,
                    $item->produto->id,
                    $aFont,
                    'T',
                    'L',
                    false,
                    '',
                    true
                );
                $this->pdf->textBox(
                    $x1,
                    $y2,
                    ($this->wPrint * $matrix[1]),
                    $it->height,
                    $item->produto->nome,
                    $aFont,
                    'T',
                    'L',
                    false,
                    '',
                    false
                );
                $this->pdf->textBox(
                    $x2,
                    $y2,
                    ($this->wPrint * $matrix[2]),
                    $it->height,
                    number_format($item->quantidade,2),
                    $aFont,
                    'T',
                    'R',
                    false,
                    '',
                    true
                );


                $item->valor_unitario = $item->produto->valor_unitario;
                
                $this->pdf->textBox(
                    $x4,
                    $y2,
                    ($this->wPrint * $matrix[4]-1),
                    $it->height,
                    number_format($item->valor_unitario, 2, ',', '.'),
                    $aFont,
                    'T',
                    'R',
                    false,
                    '',
                    true
                );
                $this->pdf->textBox(
                    $x5,
                    $y2,
                    ($this->wPrint * $matrix[5]),
                    $it->height,
                    number_format($item->valor_unitario*$item->quantidade, 2, ',', '.'),
                    $aFont,
                    'T',
                    'R',
                    false,
                    '',
                    true
                );
                $y2 += $it->height;
                $y2 += 3;
                $lenDescription = strlen($item->produto->nome);

                if($lenDescription > 10){
                    $y2+=12;
                }

            }
        }
        $this->pdf->dashedHLine($this->margem, $this->bloco3H+$y, $this->wPrint, 0.1, 30);
        return $this->bloco3H + $y;
    }
    
    protected function calculateHeightItens($descriptionWidth, $itens, $troca = null)
    {

        if ($this->flagResume) {
            return 0;
        }
        $fsize = 7;
        if ($this->paperwidth < 70) {
            $fsize = 5;
        }
        $hfont = (imagefontheight($fsize)/72)*15;
        $aFont = ['font'=> $this->fontePadrao, 'size' => $fsize, 'style' => ''];
        $htot = 0;

        foreach ($itens as $item) {

            $cProd      = $item->produto->id;
            $xProd      = substr($item->produto->nome, 0, 100);
            $qCom       = number_format((float) $item->quantidade, 2, ",", ".");
            $uCom       = $item->produto->unidade_venda;
            $vUnCom     = number_format((float) $item->valor_unitario, 2, ",", ".");
            $vProd      = number_format((float) $item->valor_unitario*$item->quantidade, 2, ",", ".");

            $tempPDF = new \NFePHP\DA\Legacy\Pdf();
            $tempPDF->setFont($this->fontePadrao, '', $fsize);

            $n = $tempPDF->wordWrap($xProd, $descriptionWidth);
            $limit = 45;

            $h = ($hfont * $n)+10.0;
            $this->itens[] = [
                "codigo" => $cProd,
                "desc" => $xProd,
                "qtd" => $qCom,
                "un" => $uCom,
                "vunit" => $vUnCom,
                "valor" => $vProd,
                "height" => $h
            ];
            $htot += $h;
        }

        if($this->troca != null){
            foreach ($this->troca->itens as $item) {

                $cProd      = $item->produto->id;
                $xProd      = substr($item->produto->nome, 0, 45);
                $qCom       = number_format((float) $item->quantidade, 2, ",", ".");
                $uCom       = $item->produto->unidade_venda;
                $vUnCom     = number_format((float) $item->valor_unitario, 2, ",", ".");
                $vProd      = number_format((float) $item->valor_unitario*$item->quantidade, 2, ",", ".");

                $tempPDF = new \NFePHP\DA\Legacy\Pdf();
                $tempPDF->setFont($this->fontePadrao, '', $fsize);

                $n = $tempPDF->wordWrap($xProd, $descriptionWidth);
                $limit = 45;

                $h = ($hfont * $n)+15.0;
                $this->itens[] = [
                    "codigo" => $cProd,
                    "desc" => $xProd,
                    "qtd" => $qCom,
                    "un" => $uCom,
                    "vunit" => $vUnCom,
                    "valor" => $vProd,
                    "height" => $h
                ];
                $htot += $h;
            }
            if(sizeof($this->troca->itens) == 0){
                $htot += 10;
            }
        }
        return $htot;
    }
}
