<?php
session_start();
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment;filename=transaktionen.csv');

// Überprüfen, ob Transaktionen in der Sitzung vorhanden sind
if (!isset($_POST['transactions']) || empty($_POST['transactions'])) {
    die("Keine Transaktionen zum Exportieren gefunden.");
}

// Transaktionen aus dem POST-Datenstring wiederherstellen
$transactions = unserialize(htmlspecialchars_decode($_POST['transactions']));

if (!is_array($transactions)) {
    die("Fehler: Die Transaktionen konnten nicht korrekt verarbeitet werden.");
}

// CSV-Datei vorbereiten
$output = fopen('php://output', 'w');

// Kopfzeile für die CSV-Datei
fputcsv($output, ['Nr.', 'Datum', 'Art der Transaktion', 'Zahlungsempfänger', 'IBAN', 'Verwendungszweck', 'Betrag'], ';');

// Daten in die CSV-Datei schreiben
foreach ($transactions as $transaction) {
    // Tausendertrennzeichen entfernen und als float interpretieren
    $amount = str_replace('.', '', $transaction['amount']); // Entferne den Punkt (Tausendertrennzeichen)
    $amount = str_replace(',', '.', $amount); // Ersetze Komma durch Punkt (Dezimaltrennzeichen)
    
    // Formatierung des Betrags für CSV
    $formattedAmount = number_format((float)$amount, 2, ',', '.');

    fputcsv($output, [
        $transaction['nr'],
        $transaction['date'],
        $transaction['type'],
        $transaction['payee'],
        $transaction['iban'],
        $transaction['purpose'],
        $formattedAmount
    ], ';');
}

fclose($output);
?>
