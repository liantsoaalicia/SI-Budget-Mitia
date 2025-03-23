<?php
// pages/list-transactions.php
// This file displays the list of transactions

require_once '../config.php';

// Get filter parameters
$departement_id = isset($_GET['departement_id']) ? (int) $_GET['departement_id'] : null;
$type = isset($_GET['type']) ? sanitize($_GET['type']) : null;
$periode_id = isset($_GET['periode_id']) ? (int) $_GET['periode_id'] : null;

// Base query
$query = "
    SELECT t.id, t.montant, t.date_transaction, 
           d.nom AS departement, 
           c.type, c.nature, c.charge_type,
           p.nom AS periode
    FROM transactions t
    JOIN departements d ON t.departement_id = d.id
    JOIN categories c ON t.categorie_id = c.id
    JOIN periodes p ON t.periode_id = p.id
    WHERE 1=1
";

$params = [];

// Add filters
if ($departement_id) {
    $query .= " AND t.departement_id = ?";
    $params[] = $departement_id;
}

if ($type) {
    $query .= " AND c.type = ?";
    $params[] = $type;
}

if ($periode_id) {
    $query .= " AND t.periode_id = ?";
    $params[] = $periode_id;
}

$query .= " ORDER BY t.date_transaction DESC, d.nom";

try {
    // Get departments for filter
    $stmt = $pdo->query("SELECT id, nom FROM departements ORDER BY nom");
    $departements = $stmt->fetchAll();

    // Get periods for filter
    $stmt = $pdo->query("SELECT id, nom FROM periodes ORDER BY id");
    $periodes = $stmt->fetchAll();

    // Get transactions
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des transactions: " . $e->getMessage();
}
?>

<div class="container">
    <h2>Liste des transactions</h2>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Filtres</h5>
        </div>
        <div class="card-body">
            <form id="filter-form" method="get">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="filter-departement" class="form-label">Département</label>
                        <select class="form-select" id="filter-departement" name="departement_id">
                            <option value="">Tous les départements</option>
                            <?php foreach ($departements as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo ($departement_id === $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo $dept['nom']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="filter-type" class="form-label">Type</label>
                        <select class="form-select" id="filter-type" name="type">
                            <option value="">Tous les types</option>
                            <option value="Dépense" <?php echo ($type === 'Dépense') ? 'selected' : ''; ?>>Dépenses
                            </option>
                            <option value="Recette" <?php echo ($type === 'Recette') ? 'selected' : ''; ?>>Recettes
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="filter-periode" class="form-label">Période</label>
                        <select class="form-select" id="filter-periode" name="periode_id">
                            <option value="">Toutes les périodes</option>
                            <?php foreach ($periodes as $periode): ?>
                                <option value="<?php echo $periode['id']; ?>" <?php echo ($periode_id === $periode['id']) ? 'selected' : ''; ?>>
                                    <?php echo $periode['nom']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <button type="button" id="reset-filters" class="btn btn-outline-secondary">Réinitialiser</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Département</th>
                        <th>Type</th>
                        <th>Nature</th>
                        <th>Période</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions) > 0): ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo $transaction['id']; ?></td>
                                <td><?php echo $transaction['departement']; ?></td>
                                <td>
                                    <span
                                        class="badge <?php echo ($transaction['type'] === 'Dépense') ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo $transaction['type']; ?>
                                    </span>
                                </td>
                                <td><?php echo $transaction['nature']; ?> (<?php echo $transaction['charge_type']; ?>)</td>
                                <td><?php echo $transaction['periode']; ?></td>
                                <td>
                                    <span
                                        class="<?php echo ($transaction['type'] === 'Dépense') ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo number_format($transaction['montant'], 2, ',', ' '); ?> €
                                    </span>
                                </td>
                                <td><?php echo $transaction['date_transaction']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary view-btn" data-id="<?php echo $transaction['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $transaction['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Aucune transaction trouvée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- View Transaction Modal -->
    <div class="modal fade" id="viewTransactionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de la transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="transaction-details">
                    <!-- Transaction details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteTransactionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation de suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cette transaction? Cette action est irréversible.</p>
                    <input type="hidden" id="delete-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Reset filters button
        $('#reset-filters').click(function () {
            $('#filter-departement').val('');
            $('#filter-type').val('');
            $('#filter-periode').val('');
            $('#filter-form').submit();
        });

        // View transaction details
        $('.view-btn').click(function () {
            const id = $(this).data('id');

            $.ajax({
                url: 'api/get_transaction.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        const transaction = response.data;
                        const typeClass = transaction.type === 'Dépense' ? 'text-danger' : 'text-success';

                        let html = `
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Département</th>
                                    <td>${transaction.departement}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td><span class="badge ${transaction.type === 'Dépense' ? 'bg-danger' : 'bg-success'}">${transaction.type}</span></td>
                                </tr>
                                <tr>
                                    <th>Nature</th>
                                    <td>${transaction.nature}</td>
                                </tr>
                                <tr>
                                    <th>Type de charge</th>
                                    <td>${transaction.charge_type}</td>
                                </tr>
                                <tr>
                                    <th>Source</th>
                                    <td>${transaction.source}</td>
                                </tr>
                                <tr>
                                    <th>Destination</th>
                                    <td>${transaction.destination}</td>
                                </tr>
                                <tr>
                                    <th>Période</th>
                                    <td>${transaction.periode}</td>
                                </tr>
                                <tr>
                                    <th>Montant</th>
                                    <td class="${typeClass}">${parseFloat(transaction.montant).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €</td>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <td>${transaction.date_transaction}</td>
                                </tr>
                            </table>
                        </div>
                    `;

                        $('#transaction-details').html(html);
                        new bootstrap.Modal(document.getElementById('viewTransactionModal')).show();
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        // Delete transaction button click
        $('.delete-btn').click(function () {
            const id = $(this).data('id');
            $('#delete-id').val(id);

            new bootstrap.Modal(document.getElementById('deleteTransactionModal')).show();
        });

        // Confirm delete button click
        $('#confirm-delete').click(function () {
            const id = $('#delete-id').val();

            $.ajax({
                url: 'api/delete_transaction.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('deleteTransactionModal')).hide();

                        // Reload page to see updated data
                        loadPageContent('list-transactions');
                    } else {
                        alert(response.message);
                    }
                }
            });
        });
    });
</script>