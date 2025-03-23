<?php
$host = 'localhost';
$dbname = 'budgetITU';
$username = 'root';
$password = '';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si un fichier a été uploadé
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
        // Dossier où stocker les fichiers uploadés
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true); // Créer le dossier s'il n'existe pas
        }

        // Chemin du fichier CSV
        $csvFile = $uploadDir . 'prevision.csv';

        // Déplacer le fichier uploadé vers le dossier d'upload
        if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $csvFile)) {
            // Ouvrir le fichier CSV
            if (($handle = fopen($csvFile, 'r')) !== FALSE) {
                // Lire la première ligne (en-têtes)
                $headers = fgetcsv($handle, 1000, ',');

                // Commencer une transaction pour garantir l'intégrité des données
                $pdo->beginTransaction();

                // Lire les lignes du fichier CSV
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    $departementNom = $data[0];
                    $chargeType = $data[1];
                    $nature = $data[2];
                    $type = $data[3];
                    $montants = array_slice($data, 4);
                    echo "<p>", $nature , " " , $type," ",$chargeType, "</p>";


                    // Récupérer l'ID du département
                    $stmt = $pdo->prepare("SELECT id FROM departements WHERE nom = :nom");
                    $stmt->execute([':nom' => $departementNom]);
                    $departement = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Si le département n'existe pas, ignorer cette ligne
                    if (!$departement) {
                        continue;
                    }
                    $departementId = $departement['id'];
                    echo "<p> d: ", $departementId, "</p>";

                    // Récupérer l'ID de la catégorie
                    $stmt = $pdo->prepare("SELECT id FROM categories WHERE nature = :nature AND type = :type AND charge_type = :charge_type");
                    $stmt->execute([
                        ':nature' => $nature,
                        ':type' => $type,
                        ':charge_type' => $chargeType
                    ]);
                    $categorie = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Si la catégorie n'existe pas, ignorer cette ligne
                    if (!$categorie) {
                        continue;
                    }
                    $categorieId = $categorie['id'];
                    echo "<p> c :", $categorieId, "</p>";

                    // Insérer ou mettre à jour les prévisions pour chaque période
                    for ($i = 0; $i < count($montants); $i++) {
                        $periodeId = $i + 1;
                        $montant = $montants[$i];

                        // Vérifier si le montant est valide
                        if (!empty($montant) && is_numeric($montant)) {
                            // Insérer ou mettre à jour la prévision
                            $stmt = $pdo->prepare("INSERT INTO previsions (departement_id, categorie_id, periode_id, montant) 
                                VALUES (:departement_id, :categorie_id, :periode_id, :montant) 
                                ON DUPLICATE KEY UPDATE montant = :montant");
                            $stmt->execute([
                                ':departement_id' => $departementId,
                                ':categorie_id' => $categorieId,
                                ':periode_id' => $periodeId,
                                ':montant' => $montant
                            ]);
                        }
                    }
                }
                fclose($handle);

                // Valider la transaction
                $pdo->commit();

                // Redirection vers la page principale avec un message de succès
                header('Location: ../index.php?success=1');
                exit();
            } else {
                throw new Exception("Impossible d'ouvrir le fichier CSV.");
            }
        } else {
            throw new Exception("Erreur lors de l'upload du fichier.");
        }
    } else {
        throw new Exception("Aucun fichier n'a été uploadé.");
    }
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Redirection vers la page principale avec un message d'erreur
    header('Location: ../index.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>