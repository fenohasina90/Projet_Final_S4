<?php 
require_once __DIR__ . '/../models/Export.php';
require_once __DIR__ . '/../helpers/Utils.php';

class ExportController {
    public static function exportData() {
        $data = Flight::request()->data;
        $exportedFile = Export::exportSimulationToPDF($data);
        
        if ($exportedFile) {
            Flight::json(['message' => 'Data exported successfully', 'file' => $exportedFile]);
        } else {
            Flight::json(['message' => 'Failed to export data'], 500);
        }
    }
}
{
    # code...
}
?>