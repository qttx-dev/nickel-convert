<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

// Überprüfen, ob Transaktionen im POST-Request vorhanden sind
if (!isset($_POST['transactions']) || empty($_POST['transactions'])) {
    echo json_encode(['status' => 'error', 'message' => 'Keine Transaktionen zum Speichern gefunden.']);
    exit;
}

// Transaktionen aus dem POST-Datenstring wiederherstellen
$transactions = json_decode($_POST['transactions'], true);

if (!is_array($transactions)) {
    echo json_encode(['status' => 'error', 'message' => 'Fehler beim Verarbeiten der Transaktionen.']);
    exit;
}

// Lade die Transaktionen aus der Sitzung
$storedTransactions = $_SESSION['transactions'] ?? [];

// Update die Transaktionen in der Sitzung
foreach ($transactions as $transaction) {
    $index = $transaction['index'];
    if (isset($storedTransactions[$index])) {
        $storedTransactions[$index]['payee'] = $transaction['payee'];
        $storedTransactions[$index]['iban'] = $transaction['iban'];
        $storedTransactions[$index]['purpose'] = $transaction['purpose'];
    }
}

$_SESSION['transactions'] = $storedTransactions;

echo json_encode(['status' => 'success']);
?>
