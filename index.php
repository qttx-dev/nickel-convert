<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nickel PDF zu CSV Konverter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Nickel PDF zu CSV Konverter</h1>
        <form action="convert.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="pdfFile" class="form-label">Nickel Kontoauszug (PDF)</label>
                <input type="file" class="form-control" id="pdfFile" name="pdfFile" accept=".pdf" required>
            </div>
            <button type="submit" class="btn btn-primary">Konvertieren</button>
        </form>
    </div>
</body>
</html>