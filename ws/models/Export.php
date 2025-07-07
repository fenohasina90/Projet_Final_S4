<?php 
require_once __DIR__ . '/../db.php';
require('../inc/fpdf186/fpdf.php');

class Export
{
    public static function exportSimulationToPDF($data) {
        // Création d'une nouvelle instance de FPDF
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Encodage pour supporter les caractères français
        $pdf->SetFont('Arial', 'B', 16);
        
        // Titre du document
        $pdf->Cell(0, 10, 'Récapitulatif de votre simulation', 0, 1, 'C');
        $pdf->Ln(10);
        
        // Police normale pour le contenu
        $pdf->SetFont('Arial', '', 12);
        
        // Affichage des données
        $pdf->Cell(0, 10, 'Besoin: ' . $data['besoin'], 0, 1);
        $pdf->Cell(0, 10, 'Montant du prêt: ' . $data['montant'] . ' MGA', 0, 1);
        $pdf->Cell(0, 10, 'Coût total du prêt: ' . $data['cout_total'] . ' MGA', 0, 1);
        $pdf->Cell(0, 10, 'Durée du prêt: ' . $data['duree'], 0, 1);
        $pdf->Cell(0, 10, 'Mensualité à payer: ' . $data['mensualite'] . ' MGA', 0, 1);
        
        // Pied de page
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Généré le ' . date('d/m/Y'), 0, 0, 'C');
        
        // Sortie du PDF
        $pdf->Output('I', 'Recapitulatif_Simulation.pdf');
    }

}



?>