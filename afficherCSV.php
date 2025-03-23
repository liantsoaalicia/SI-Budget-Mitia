<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affichage CSV avec Bootstrap 5</title>
    <!-- Intégration de Bootstrap 5 CSS -->
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Affichage du fichier CSV</h1>
        <?php
      
        $csvFile = 'Departemenet.csv';

        if (!file_exists($csvFile)) {
            echo '<div class="alert alert-danger" role="alert">Le fichier CSV n\'existe pas.</div>';
            exit;
        }

        $file = fopen($csvFile, 'r');

        echo '<table class="table table-bordered table-striped">';

        $headers = fgetcsv($file, 1000, ';');
        if ($headers) {
            echo '<thead class="table-dark"><tr>';
            foreach ($headers as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            echo '</tr></thead>';
        }

        echo '<tbody>';
        while (($data = fgetcsv($file, 1000, ',')) !== false) {
            echo '<tr>';
            foreach ($data as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody>';

        echo '</table>';

        fclose($file);
        ?>
    </div>

    <!-- Intégration de Bootstrap 5 JS (optionnel, pour les fonctionnalités avancées) -->
</body>
</html>