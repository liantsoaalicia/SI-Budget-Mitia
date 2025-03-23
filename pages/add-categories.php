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

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (type, charge_type, nature, source, destination) 
            VALUES (:type, :charge_type, :nature, :source, :destination)");
        $stmt->execute([
            'type' => $_POST['type'],
            'charge_type' => $_POST['charge_type'],
            'nature' => $_POST['nature'],
            'source' => $_POST['source'],
            'destination' => $_POST['destination']
        ]);
        $success = "Catégorie ajoutée avec succès!";
    } catch (Exception $e) {
        $error = "Erreur lors de l'ajout: " . $e->getMessage();
    }
}
?>

    <div class="container py-4">
        <h1 class="mb-4">Ajouter une Nouvelle Catégorie</h1>

        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Type</label>
                <select name="type" class="form-select" required>
                    <option value="">Sélectionnez un type</option>
                    <option value="Dépense">Dépense</option>
                    <option value="Recette">Recette</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Type de charge</label>
                <select name="charge_type" class="form-select" required>
                    <option value="">Sélectionnez un type de charge</option>
                    <option value="Charges fixes">Charges fixes</option>
                    <option value="Charges variables">Charges variables</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nature</label>
                <input type="text" name="nature" class="form-control" placeholder="Ex: Frais de scolarité" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Source</label>
                <input type="text" name="source" class="form-control" placeholder="Ex: Étudiants" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Destination</label>
                <input type="text" name="destination" class="form-control" placeholder="Ex: Fonctionnement" required>
            </div>
            <div class="col-12">
                <button type="submit" name="add_category" class="btn btn-primary">Ajouter la catégorie</button>
                <a href="list_categories.php" class="btn btn-secondary">Voir la liste</a>
            </div>
        </form>
    </div>
