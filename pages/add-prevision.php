<?php
$host = 'localhost';
$dbname = 'budgetITU';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Récupérer les données pour le formulaire
$departements = $pdo->query("SELECT * FROM departements ORDER BY nom")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY nature")->fetchAll();
$periodes = $pdo->query("SELECT * FROM periodes ORDER BY date_debut")->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_previsions'])) {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO previsions (departement_id, categorie_id, periode_id, montant) 
            VALUES (:dept_id, :cat_id, :periode_id, :montant) 
            ON DUPLICATE KEY UPDATE montant = :montant");
            
        foreach ($_POST['previsions'] as $dept_id => $dept_data) {
            foreach ($dept_data as $cat_id => $cat_data) {
                foreach ($cat_data as $periode_id => $montant) {
                    if (!empty($montant) && is_numeric($montant)) {
                        $stmt->execute([
                            'dept_id' => $dept_id,
                            'cat_id' => $cat_id,
                            'periode_id' => $periode_id,
                            'montant' => $montant
                        ]);
                    }
                }
            }
        }
        
        $pdo->commit();
        $success = "Prévisions enregistrées avec succès!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Erreur lors de l'enregistrement: " . $e->getMessage();
    }
}

// Récupérer les prévisions existantes pour affichage
$previsions = $pdo->query("SELECT p.*, d.nom as dept_nom, c.nature as cat_nature, per.nom as periode_nom 
    FROM previsions p 
    JOIN departements d ON p.departement_id = d.id 
    JOIN categories c ON p.categorie_id = c.id 
    JOIN periodes per ON p.periode_id = per.id 
    ORDER BY d.nom, c.nature, per.date_debut")->fetchAll();
?>

<div class="container">

        <h1 class="mb-4">Gestion des Prévisions Budgétaires</h1>

        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Formulaire d'insertion -->
        <form method="POST" action="">
            <div class="table-container">
                
            </div>
            <button type="submit" name="submit_previsions" class="btn btn-primary mt-3">
                Enregistrer toutes les prévisions
            </button>
        </form>

        <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header text-center bg-primary text-white">
                        <h4>Importer un fichier CSV</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="pages/traitement-csv.php" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Sélectionner un fichier CSV</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv"
                                    required>
                                <div class="form-text text-muted">Le fichier doit être au format CSV.</div>
                            </div>
                            <button type="submit" name="upload_csv" class="btn btn-primary w-100 mt-3">
                                Importer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <?php
    // if (isset($_GET['success'])) {
//     echo '<div class="alert alert-success">Le fichier CSV a été importé avec succès !</div>';
// }
// if (isset($_GET['error'])) {
//     echo '<div class="alert alert-danger">Erreur : ' . htmlspecialchars($_GET['error']) . '</div>';
// }
    ?> -->

        <!-- Affichage des prévisions enregistrées -->
        <h2 class="mt-5">Prévisions Enregistrées</h2>
        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead class="table-sticky">
                    <tr>
                        <th>Département</th>
                        <th>Catégorie</th>
                        <th>Période</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($previsions as $prev): ?>
                        <tr>
                            <td><?= htmlspecialchars($prev['dept_nom']) ?></td>
                            <td><?= htmlspecialchars($prev['cat_nature']) ?></td>
                            <td><?= htmlspecialchars($prev['periode_nom']) ?></td>
                            <td><?= number_format($prev['montant'], 2, ',', ' ') ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
