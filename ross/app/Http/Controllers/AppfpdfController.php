<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;

class AppfpdfController extends Fpdf {

    function Header() {
        # Tamaño carta
        # Largo: 216
        # Alto: 279
        $obj = new \App\Http\Controllers\ResourcesController;
        $hoy = $obj->getStringDate();
        $titulo = "ROSS - Regional Operations Support System";

        if($this->PageNo()==1){
            #una portada
        }

        $this->Image(url('img/body3.png'), 0, 0, 216, 280);# Fondo de página.
        $this->Image(url('img/logo-white.png'), 10, 8, 60, 10);# Logo de página.

        # Linea vertical
        $this->SetLineWidth(0.3);
        $this->SetDrawColor( 108, 118, 128 );
        $this->Line( 80, 2, 80, 18 );

        $this->setY(6); 
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor( 255, 255, 255 );
        $this->MultiCell(186, 6, $titulo, 0, 'R', false);
        
        $this->SetFont('Arial', 'I', 10);
        $this->MultiCell(186, 5, env('URL_DOMINIO', ''), 0, 'R', false);
        $this->MultiCell(186, 5, $hoy, 0, 'R', false);

        $this->setXY(15, 36); 
    }

    function Footer() {
        $obj = new \App\Http\Controllers\ResourcesController;
        $hoy = $obj->getStringDate();
        $info_foother = 'Pag. '.$this->PageNo().' de {nb} | '.$hoy.' '.date("H:m").' | '.config('appross.broadband', '');
        $aaa = 'Page '.$this->PageNo().'/{nb}';
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor( 86, 86, 86 );
        #$this->Cell(0,10, $aaa, 1, 1, 'C');
        $this->Cell(0, 10, $info_foother, 0, 1, 'C');
    }

}