<?php
// pages/add-transaction.php
// This file handles adding new transactions (expenses or income)

require_once '../config.php';

// Get departments
try {
    $stmt = $pdo->query("SELECT id, nom FROM departements ORDER BY nom");
    $departements = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des départements: " . $e->getMessage();
}

// Get categories
try {
    $stmt = $pdo->query("SELECT id, type, charge_type, nature, source, destination FROM categories ORDER BY type, nature");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des catégories: " . $e->getMessage();
}

// Get periods
try {
    $stmt = $pdo->query("SELECT id, nom FROM periodes ORDER BY id");
    $periodes = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des périodes: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    $departement_id = (int) $_POST['departement_id'];
    $categorie_id = (int) $_POST['categorie_id'];
    $periode_id = (int) $_POST['periode_id'];
    $montant = (float) $_POST['montant'];

    if ($montant <= 0) {
        $error = "Le montant doit être supérieur à zéro.";
    } else {
        try {
            // Insert transaction
            $stmt = $pdo->prepare("INSERT INTO transactions (departement_id, categorie_id, periode_id, montant) VALUES (?, ?, ?, ?)");
            $stmt->execute([$departement_id, $categorie_id, $periode_id, $montant]);

            // Update solde for this department and period
            $pdo->beginTransaction();

            // Get transaction type (expense or income)
            $stmt = $pdo->prepare("SELECT type FROM categories WHERE id = ?");
            $stmt->execute([$categorie_id]);
            $category = $stmt->fetch();

            // Get current solde for this department and period
            $stmt = $pdo->prepare("SELECT id, solde_debut, solde_fin FROM soldes WHERE departement_id = ? AND periode_id = ?");
            $stmt->execute([$departement_id, $periode_id]);
            $solde = $stmt->fetch();

            if ($solde) {
                // Update existing solde
                $new_solde_fin = $solde['solde_fin'];

                if ($category['type'] === 'Recette') {
                    $new_solde_fin = $solde['solde_fin'] + $montant;
                } else {
                    $new_solde_fin = $solde['solde_fin'] - $montant;
                }

                $stmt = $pdo->prepare("UPDATE soldes SET solde_fin = ? WHERE id = ?");
                $stmt->execute([$new_solde_fin, $solde['id']]);

                // Update next period's solde_debut if it exists
                $next_periode_id = $periode_id + 1;
                $stmt = $pdo->prepare("SELECT id FROM soldes WHERE departement_id = ? AND periode_id = ?");
                $stmt->execute([$departement_id, $next_periode_id]);
                $next_solde = $stmt->fetch();

                if ($next_solde) {
                    $stmt = $pdo->prepare("UPDATE soldes SET solde_debut = ? WHERE id = ?");
                    $stmt->execute([$new_solde_fin, $next_solde['id']]);
                }

    
            } else {
                // Create new solde entry
                $solde_debut = 0;
                $solde_fin = ($category['type'] === 'Recette') ? $montant : -$montant;

                $stmt = $pdo->prepare("INSERT INTO soldes (departement_id, periode_id, solde_debut, solde_fin) VALUES (?, ?, ?, ?)");
                $stmt->execute([$departement_id, $periode_id, $solde_debut, $solde_fin]);

                // Check if there's a previous period with a solde
                if ($periode_id > 1) {
                    $prev_periode_id = $periode_id - 1;
                    $stmt = $pdo->prepare("SELECT solde_fin FROM soldes WHERE departement_id = ? AND periode_id = ?");
                    $stmt->execute([$departement_id, $prev_periode_id]);
                    $prev_solde = $stmt->fetch();

                    if ($prev_solde) {
                        // Update the current period's solde_debut with previous period's solde_fin
                        $stmt = $pdo->prepare("UPDATE soldes SET solde_debut = ? WHERE departement_id = ? AND periode_id = ?");
                        $stmt->execute([$prev_solde['solde_fin'], $departement_id, $periode_id]);

                        // Recalculate solde_fin
                        $new_solde_fin = $prev_solde['solde_fin'] + (($category['type'] === 'Recette') ? $montant : -$montant);
                        $stmt = $pdo->prepare("UPDATE soldes SET solde_fin = ? WHERE departement_id = ? AND periode_id = ?");
                        $stmt->execute([$new_solde_fin, $departement_id, $periode_id]);
                    }
                }
            }

            $pdo->commit();
            $success = "Transaction ajoutée avec succès!";

            // If AJAX request, return JSON response
            if (is_ajax_request()) {
                json_response(true, $success);
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur lors de l'ajout de la transaction: " . $e->getMessage();

            // If AJAX request, return JSON response
            if (is_ajax_request()) {
                json_response(false, $error);
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['transaction_csv'])) {
    // Générer du code JavaScript pour afficher un message dans la console
    echo "<script>console.log('Message envoyé depuis PHP');</script>";
    $file = $_FILES['transaction_csv']['tmp_name'];
    $errors = [];
    $success_count = 0;

    if (($handle = fopen($file, "r")) !== FALSE) {
       
        fgetcsv($handle, 1000, ",");

        $stmt = $pdo->prepare("
            INSERT INTO transactions (departement_id, categorie_id, periode_id, montant, date_transaction)
            VALUES (:dept_id, :cat_id, :periode_id, :montant, :date_transaction)
        ");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Vérifier que la ligne contient le bon nombre de colonnes (5)
            if (count($data) != 5) {
                $errors[] = "Ligne invalide : " . implode(",", $data);
                continue;
            }
            list($dept_id, $cat_id, $periode_id, $montant, $date_transaction) = $data;
            // Validation des données
            if (!is_numeric($dept_id) || !is_numeric($cat_id) || !is_numeric($periode_id) || !is_numeric($montant)) {
                $errors[] = "Données non numériques détectées : " . implode(",", $data);
                continue;
            }

            try {
                $stmt->execute([
                    'dept_id' => $dept_id,
                    'cat_id' => $cat_id,
                    'periode_id' => $periode_id,
                    'montant' => $montant,
                    'date_transaction' => $date_transaction ?: date('Y-m-d H:i:s')
                ]);
                $success_count++;
            } catch (PDOException $e) {
                $errors[] = "Erreur lors de l'insertion : " . implode(",", $data) . " - " . $e->getMessage();
            }
        }
        fclose($handle);

        if ($success_count > 0) {
            $success = "Importation réussie : $success_count transactions ajoutées.";
        }
    } else {
        $errors[] = "Erreur lors de l'ouverture du fichier CSV.";
    }
}
?>
<div class="container">
    <h2>Ajouter une transaction</h2>
    <div id="message-container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>
    <form id="add-transaction-form" method="post">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="departement_id" class="form-label">Département</label>
                <select class="form-select" id="departement_id" name="departement_id" required>
                    <option value="">Sélectionner un département</option>
                    <?php foreach ($departements as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>"><?php echo $dept['nom']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

              <div class="col-md-6 mb-3">
                <label for="categorie_id" class="form-label">Catégorie</label>
                <select class="form-select" id="categorie_id" name="categorie_id" required>
                    <option value="">Sélectionner une catégorie</option>
                    <optgroup label="Dépenses">
                        <?php foreach ($categories as $cat): ?>
                            <?php if ($cat['type'] === 'Dépense'): ?>
                                <option value="<?php echo $cat['id']; ?>" data-type="<?php echo $cat['type']; ?>">
                                    <?php echo $cat['nature']; ?> (<?php echo $cat['charge_type']; ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Recettes">
                        <?php foreach ($categories as $cat): ?>
                            <?php if ($cat['type'] === 'Recette'): ?>
                                <option value="<?php echo $cat['id']; ?>" data-type="<?php echo $cat['type']; ?>">
                                    <?php echo $cat['nature']; ?> (<?php echo $cat['charge_type']; ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="periode_id" class="form-label">Période</label>
                <select class="form-select" id="periode_id" name="periode_id" required>
                    <option value="">Sélectionner une période</option>
                    <?php foreach ($periodes as $periode): ?>
                        <option value="<?php echo $periode['id']; ?>"><?php echo $periode['nom']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="montant" class="form-label">Montant</label>
                <div class="input-group">
                    <input type="number" class="form-control" id="montant" name="montant" step="0.01" min="0.01"
                        required>
                    <span class="input-group-text" id="transaction-type-label">Ar</span>

                </div>
            </div>
        </div>

        <button type="submit" name="add_transaction" class="btn btn-primary">Ajouter la transaction</button>
    </form>

    <br>
    <br>
    <br>
    <!-- Formulaire d'importation CSV -->
    <form id="add-transaction-csv" method="POST" enctype="multipart/form-data" class="mb-4">
            <div class="mb-3">
                <label for="transaction_csv" class="form-label">Importer un fichier CSV</label>
                <input type="file" class="form-control" id="transaction_csv" name="transaction_csv" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-primary">Importer Transactions</button>
        </form>
</div>

<script>
    $(document).ready(function () {
        $('#categorie_id').change(function () {
            const selectedOption = $(this).find('option:selected');
            const type = selectedOption.data('type');

            if (type === 'Dépense') {
                $('#transaction-type-label').text('€ (Dépense)');
            } else if (type === 'Recette') {
                $('#transaction-type-label').text('€ (Recette)');
            } else {
                $('#transaction-type-label').text('€');
            }
        });

        $('#add-transaction-csv').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: 'pages/add-transaction.php', // URL du même fichier qui traite l'importation
                type: 'POST',
                data: formData,
                dataType: 'json', // Attendre une réponse JSON
                processData: false, // Ne pas traiter les données (nécessaire pour FormData)
                contentType: false, // Ne pas définir le type de contenu (FormData le fait automatiquement)
                success: function(response) {
                    let alertClass = response.success ? 'alert-success' : 'alert-danger';
                    let messageHtml = `
                        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('#message-container').html(messageHtml);

                    if (response.success) {
                        $('#import-transaction-form')[0].reset(); // Réinitialiser le formulaire
                    }
                },
                error: function(xhr, status, error) {
                    $('#message-container').html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Une erreur est survenue lors de la communication avec le serveur: ${error}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                }
            });
        });

        $('#add-transaction-form').on('submit', function (e) {
            e.preventDefault();

            $.ajax({
                url: 'pages/add-transaction.php',
                type: 'POST',
                data: $(this).serialize() + '&add_transaction=1',
                dataType: 'json',
                success: function (response) {
                    let alertClass = response.success ? 'alert-success' : 'alert-danger';
                    $('#message-container').html(`
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${response.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
                    if (response.success) {
                        $('#add-transaction-form')[0].reset();
                        $('#transaction-type-label').text('€');
                    }
                },
                error: function () {
                    $('#message-container').html(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Une erreur est survenue lors de la communication avec le serveur.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
                }
            });
        });
    });
</script>