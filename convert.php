<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require 'vendor/autoload.php';
use Smalot\PdfParser\Parser;

require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
    $pdfParser = new Parser();
    try {
        $pdf = $pdfParser->parseFile($_FILES['pdfFile']['tmp_name']);
        $pages = $pdf->getPages();
        $texts = [];

        foreach ($pages as $page) {
            $texts[] = $page->getText();
        }

        $transactions = extractTransactionsFromPages($texts);
        $_SESSION['transactions'] = $transactions; // Speichere die Transaktionen in der Sitzung
    } catch (Exception $e) {
        die("Fehler beim Verarbeiten der PDF-Datei: " . $e->getMessage());
    }
}

// Lade Transaktionen aus der Sitzung, wenn sie existieren
$transactions = $_SESSION['transactions'] ?? [];

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gefundene Transaktionen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .save-btn {
            margin: 5px;
        }
        .btn-success {
            margin-left: 15px;
        }
        textarea {
            width: 100%;
            box-sizing: border-box;
        }
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            min-width: 250px;
            padding: 15px;
            background-color: #333;
            color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            opacity: 0;
            visibility: hidden;
            transform: translateY(100px);
            transition: opacity 0.5s, transform 0.5s, visibility 0.5s;
        }
        .toast.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .btn-success {
            width: 100%;
        }
        .save-btn.btn-success {
            width: 120px; /* Button width */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Gefundene Transaktionen:</h2>
        <table id="transactionsTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Nr.</th>
                    <th>Datum</th>
                    <th>Art der Transaktion</th>
                    <th>Zahlungsempfänger</th>
                    <th>IBAN</th>
                    <th>Verwendungszweck</th>
                    <th>Betrag</th>
                    <th>Aktion</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $i => $transaction): ?>
                    <?php
                    $payeeName = htmlspecialchars($transaction['payee'], ENT_QUOTES, 'UTF-8');
                    $ibanValue = htmlspecialchars($transaction['iban'], ENT_QUOTES, 'UTF-8');
                    $purposeValue = htmlspecialchars($transaction['purpose'], ENT_QUOTES, 'UTF-8');
                    $amountColor = $transaction['amount'] >= 0 ? 'green' : 'red';
                    // Sicherstellen, dass die Beträge korrekt formatiert werden
                    $amountFormatted = number_format($transaction['amount'], 2, ',', '.');
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['nr'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($transaction['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($transaction['type'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><input type="text" name="payee[<?php echo $i; ?>]" value="<?php echo $payeeName; ?>" /></td>
                        <td><input type="text" name="iban[<?php echo $i; ?>]" value="<?php echo $ibanValue; ?>" /></td>
                        <td><textarea name="purpose[<?php echo $i; ?>]"><?php echo $purposeValue; ?></textarea></td>
                        <td style="color: <?php echo $amountColor; ?>"><?php echo $amountFormatted; ?> €</td>
                        <td><button class="btn btn-primary save-btn" data-index="<?php echo $i; ?>">Speichern</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form method="post" action="download.php" class="mt-3">
            <input type="hidden" name="transactions" value="<?php echo htmlspecialchars(serialize($transactions), ENT_QUOTES, 'UTF-8'); ?>" />
            <button type="submit" class="btn btn-success">CSV herunterladen</button>
        </form>
    </div>

    <script>
    $(document).ready(function() {
        function saveTransaction(index, payee, iban, purpose) {
            $.ajax({
                url: 'save_edits.php',
                type: 'POST',
                data: {
                    transactions: JSON.stringify([{
                        index: index,
                        payee: payee,
                        iban: iban,
                        purpose: purpose
                    }])
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        var button = $('.save-btn[data-index="' + index + '"]');
                        button.text('Gespeichert').removeClass('btn-primary').addClass('btn-success');
                        showToast('Änderungen gespeichert.');
                    } else {
                        showToast('Fehler beim Speichern: ' + response.message);
                    }
                },
                error: function() {
                    showToast('Fehler beim Speichern');
                }
            });
        }

        function saveAllTransactions() {
            var transactions = [];
            $('#transactionsTable tbody tr').each(function() {
                var index = $(this).find('button.save-btn').data('index');
                var row = $(this);
                var payee = row.find('input[name="payee[' + index + ']"]').val();
                var iban = row.find('input[name="iban[' + index + ']"]').val();
                var purpose = row.find('textarea[name="purpose[' + index + ']"]').val();

                transactions.push({
                    index: index,
                    payee: payee,
                    iban: iban,
                    purpose: purpose
                });
            });

            $.ajax({
                url: 'save_edits.php',
                type: 'POST',
                data: {
                    transactions: JSON.stringify(transactions)
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showToast('Alle Änderungen wurden erfolgreich gespeichert.');
                    } else {
                        showToast('Fehler beim Speichern: ' + response.message);
                    }
                },
                error: function() {
                    showToast('Fehler beim Speichern');
                }
            });
        }

        $('#saveAllBtn').click(function() {
            saveAllTransactions();
        });

        $('#transactionsTable').on('click', '.save-btn', function() {
            var index = $(this).data('index');
            var row = $(this).closest('tr');
            var payee = row.find('input[name="payee[' + index + ']"]').val();
            var iban = row.find('input[name="iban[' + index + ']"]').val();
            var purpose = row.find('textarea[name="purpose[' + index + ']"]').val();

            saveTransaction(index, payee, iban, purpose);
        });

        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('show');
            }, 100);

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 500);
            }, 3000);
        }
    });
    </script>
</body>
</html>
