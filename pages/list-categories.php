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

// Traitement de la suppression
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute(['id' => $_GET['delete']]);
        $success = "Catégorie supprimée avec succès!";
    } catch (Exception $e) {
        $error = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    try {
        $stmt = $pdo->prepare("UPDATE categories SET 
            type = :type, 
            charge_type = :charge_type, 
            nature = :nature, 
            source = :source, 
            destination = :destination 
            WHERE id = :id");
        $stmt->execute([
            'id' => $_POST['id'],
            'type' => $_POST['type'],
            'charge_type' => $_POST['charge_type'],
            'nature' => $_POST['nature'],
            'source' => $_POST['source'],
            'destination' => $_POST['destination']
        ]);
        $success = "Catégorie mise à jour avec succès!";
    } catch (Exception $e) {
        $error = "Erreur lors de la mise à jour: " . $e->getMessage();
    }
}

// Récupérer toutes les catégories
$categories = $pdo->query("SELECT * FROM categories ORDER BY nature")->fetchAll();
?>


    <div class="container py-4">
        <h1 class="mb-4">Liste des Catégories</h1>

        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <a href="add_category.php" class="btn btn-primary mb-3">Ajouter une catégorie</a>

        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Type de charge</th>
                    <th>Nature</th>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <?php if (isset($_GET['edit']) && $_GET['edit'] == $cat['id']): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <td>
                                    <select name="type" class="form-select" required>
                                        <option value="Dépense" <?= $cat['type'] == 'Dépense' ? 'selected' : '' ?>>Dépense</option>
                                        <option value="Recette" <?= $cat['type'] == 'Recette' ? 'selected' : '' ?>>Recette</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="charge_type" class="form-select" required>
                                        <option value="Charges fixes" <?= $cat['charge_type'] == 'Charges fixes' ? 'selected' : '' ?>>Charges fixes</option>
                                        <option value="Charges variables" <?= $cat['charge_type'] == 'Charges variables' ? 'selected' : '' ?>>Charges variables</option>
                                    </select>
                                </td>
                                <td><input type="text" name="nature" class="form-control" value="<?= htmlspecialchars($cat['nature']) ?>" required></td>
                                <td><input type="text" name="source" class="form-control" value="<?= htmlspecialchars($cat['source']) ?>" required></td>
                                <td><input type="text" name="destination" class="form-control" value="<?= htmlspecialchars($cat['destination']) ?>" required></td>
                                <td>
                                    <button type="submit" name="update_category" class="btn btn-success btn-sm">Enregistrer</button>
                                    <a href="list_categories.php" class="btn btn-secondary btn-sm">Annuler</a>
                                </td>
                            </form>
                        <?php else: ?>
                            <td><?= htmlspecialchars($cat['type']) ?></td>
                            <td><?= htmlspecialchars($cat['charge_type']) ?></td>
                            <td><?= htmlspecialchars($cat['nature']) ?></td>
                            <td><?= htmlspecialchars($cat['source']) ?></td>
                            <td><?= htmlspecialchars($cat['destination']) ?></td>
                            <td>
                                <a href="?edit=<?= $cat['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                                <a href="?delete=<?= $cat['id'] ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Voulez-vous vraiment supprimer cette catégorie ?');">Supprimer</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
