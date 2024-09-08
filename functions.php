<?php

function extractTransactionsFromPages($texts) {
    $transactions = [];
    
    if (!is_array($texts)) {
        return $transactions; // Falls $texts kein Array ist, gib ein leeres Array zurück
    }

    foreach ($texts as $text) {
        $pageTransactions = extractTransactions($text);
        $transactions = array_merge($transactions, $pageTransactions); // Füge die Transaktionen von jeder Seite zusammen
    }

    return $transactions;
}

function extractTransactions($text) {
    $transactions = [];
    $lines = explode("\n", $text);
    $isTransactionSection = false;
    $currentTransactionLines = [];
    $transactionPattern = '/^(\d{1,4})\s*(\d{2}\/\d{2}\/\d{4}|\d{4}-\d{2}-\d{2})\s*([A-ZÄÖÜ]+)\s+(.*?)([-]?\d+,\d{2})\s*€$/';
    $ibanPattern = '/DE[0-9]{20}/'; // Muster für IBAN

    foreach ($lines as $line) {
        // Suche nach dem Beginn des Transaktionsabschnitts
        if (strpos($line, "IHRE TRANSAKTIONEN") !== false || strpos($line, "IHRE TRANSAKTIONEN (WEITER)") !== false) {
            $isTransactionSection = true;
            continue;
        }

        // Überspringe die Kopfzeile
        if ($isTransactionSection && strpos($line, "NR.DATUM ART DER TRANSAKTION VERWENDUNGSZWECK") !== false) {
            continue;
        }

        // Beende die Extraktion, wenn wir das Ende des Transaktionsabschnitts erreichen
        if ($isTransactionSection && (empty(trim($line)) || strpos($line, "TRANSAKTIONEN ZEITVERSETZTE ABBUCHUNG") !== false)) {
            break;
        }

        // Sammle Zeilen für eine mögliche Transaktion
        if ($isTransactionSection) {
            $currentTransactionLines[] = trim($line);

            // Überprüfen, ob die letzte Zeile mit einem Euro-Zeichen endet
            if (strpos($line, '€') !== false) {
                // Füge alle gesammelten Zeilen zu einem einzigen String zusammen
                $currentTransaction = implode(' ', $currentTransactionLines);
                $currentTransaction = preg_replace('/\s+/', ' ', $currentTransaction); // Mehrfache Leerzeichen ersetzen

                // Überprüfen, ob der zusammengesetzte String dem Muster entspricht
                if (preg_match($transactionPattern, $currentTransaction, $matches)) {
                    $transactionNumber = trim($matches[1]);
                    $dateString = trim($matches[2]);
                    $purpose = trim($matches[4]);

                    // Verarbeitung des Datums basierend auf dem Format
                    if (strpos($dateString, '/') !== false) {
                        list($day, $month, $year) = explode('/', $dateString);
                    } else {
                        list($year, $month, $day) = explode('-', $dateString);
                    }

                    // Formatieren des Datums
                    $formattedDate = sprintf('%02d.%02d.%04d', $day, $month, $year);

                    // Extrahiere die IBAN
                    $iban = '';
                    if (preg_match($ibanPattern, $purpose, $ibanMatches)) {
                        $iban = $ibanMatches[0];
                        // Entferne die IBAN aus dem Verwendungszweck
                        $purpose = str_replace($iban, '', $purpose);
                    }

                    // Erstelle eine neue Transaktion
                    $transactions[] = [
                        'nr' => $transactionNumber,
                        'date' => $formattedDate,
                        'type' => trim($matches[3]),
                        'payee' => '', // Platz für den Zahlungsempfänger, der vom Benutzer bearbeitet wird
                        'iban' => $iban,
                        'purpose' => trim($purpose),
                        'amount' => str_replace(',', '.', trim($matches[5])) // Betrag konvertieren
                    ];
                }

                // Setze die Zeilen zurück
                $currentTransactionLines = [];
            }
        }
    }

    return $transactions;
}

function formatTransactionsToCSV($transactions) {
    $output = fopen('php://temp', 'r+');
    
    // UTF-8 BOM für die CSV-Datei hinzufügen
    fwrite($output, "\xEF\xBB\xBF");

    // Header für die CSV-Datei
    fputcsv($output, ['Nr.', 'Datum', 'Art der Transaktion', 'Zahlungsempfänger', 'IBAN', 'Verwendungszweck', 'Betrag']);

    foreach ($transactions as $transaction) {
        // Konvertiere das Datum in das gewünschte Format
        $formattedDate = date('d.m.Y', strtotime($transaction['date']));
        $row = [
            $transaction['nr'],
            $formattedDate,
            $transaction['type'],
            $transaction['payee'], // Zahlungsempfänger
            $transaction['iban'],
            $transaction['purpose'],
            $transaction['amount']
        ];
        fputcsv($output, $row);
    }

    rewind($output);
    return stream_get_contents($output);
}

function printUnrecognizedTransactions($text) {
    $unrecognizedTransactions = [];
    $lines = explode("\n", $text);
    $isTransactionSection = false;

    foreach ($lines as $line) {
        if (strpos($line, "IHRE TRANSAKTIONEN") !== false || strpos($line, "IHRE TRANSAKTIONEN (WEITER)") !== false) {
            $isTransactionSection = true;
            continue;
        }

        if ($isTransactionSection && empty(trim($line)) || strpos($line, "TRANSAKTIONEN ZEITVERSETZTE ABBUCHUNG") !== false) {
            break;
        }

        if ($isTransactionSection && !preg_match('/^(\d{1,4})\s*(\d{2}\/\d{2}\/\d{4}|\d{4}-\d{2}-\d{2})\s*([A-ZÄÖÜ]+)\s+(.*?)([-]?\d+,\d{2})\s*€$/', $line)) {
            $unrecognizedTransactions[] = trim($line);
        }
    }

    if (!empty($unrecognizedTransactions)) {
        echo "Unbekannte Transaktionen:\n";
        foreach ($unrecognizedTransactions as $transaction) {
            echo $transaction . "\n";
        }
    }
}
?>
