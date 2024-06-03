<?php
global $conn;
include '../includes/db.php';
include 'Test.php';
include 'ExporterInterface.php';
include 'CSVExporter.php';
include 'XMLExporter.php';
include 'GIFTExporter.php';

use Export\Test;
use Export\CSVExporter;
use Export\XMLExporter;
use Export\GIFTExporter;

if (!isset($_GET['test_id']) || !isset($_GET['format'])) {
    die('Test ID or format not provided');
}

$test_id = $_GET['test_id'];
$format = $_GET['format'];

try {
    $test = new Test($conn, $test_id);
    switch ($format) {
        case 'csv':
            $exporter = new CSVExporter();
            break;
        case 'xml':
            $exporter = new XMLExporter();
            break;
        case 'gift':
            $exporter = new GIFTExporter();
            break;
        default:
            throw new Exception('Invalid format');
    }
    ob_clean(); // Clear any existing output buffer
    $exporter->export($test);
} catch (Exception $e) {
    header('Content-Type: text/plain');
    echo "Error: " . $e->getMessage();
    exit;
}
?>
