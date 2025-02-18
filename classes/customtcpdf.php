<?php
/**
 * Created by PhpStorm.
 * User: ciber
 * Date: 14/05/2023
 * Time: 17:27
 */

require __DIR__ . '/../lib/vendor/autoload.php';

class CustomPDF extends TCPDF {
    private $headerImage;
    private $footerText;

    public function __construct($headerImage, $school, $page_format='A4',$textHeader = '', $typeReport='normal') {
        parent::__construct('P', 'mm', $page_format, true, 'UTF-8', false);
        $this->headerImage = $headerImage;
        $this->footerText = $school;
        // $this->typeReport = $typeReport;
        // $this->textHeader = $textHeader;
    }

    public function Header() {

        // if ($this->typeReport == 'normal') {
        //     $this->Image($this->headerImage, 125, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // } elseif ($this->typeReport == 'bimester') {

        // $this->SetTextColor(27, 131, 255);
        // $this->SetXY(15, 13); // Coordenadas (x, y) del texto en el encabezado
        // $this->SetFont('helvetica', 'B', 13);
        // // Establecer la imagen en el header
        // $this->Cell(125, 30, $this->textHeader, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // $this->Image($this->headerImage, 245, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

        // }

    }

    public function Footer() {
        // Establecer la imagen en el footer

        // Establecer el texto personalizado en el footer
        // $this->SetY(-25);
        // $this->Cell(0, 10, $this->footerText->school, 0, false, 'C', 0, '', 0, false, 'T', 'M');
        // $this->SetY(-20);
        // $this->Cell(0, 10, $this->footerText->dir, 0, false, 'C', 0, '', 0, false, 'T', 'M');
        // $this->SetY(-15);
        // $this->Cell(0, 10, $this->footerText->code, 0, false, 'C', 0, '', 0, false, 'T', 'M');
        // $this->SetY(-10);
        // $this->Cell(0, 10, $this->footerText->www, 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}