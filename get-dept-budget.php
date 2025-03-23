<?php
// Database Connection
 $host = 'localhost';
 $dbname = 'budgetITU';
 $user = 'root';
 $password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Validate dept_id
if (!isset($_POST['dept_id']) || empty($_POST['dept_id'])) {
    echo '<div class="alert alert-warning">Veuillez sélectionner un département.</div>';
    exit;
}

$dept_id = (int)$_POST['dept_id'];

// Fetch department name
$stmt = $pdo->prepare("SELECT nom FROM departements WHERE id = ?");
$stmt->execute([$dept_id]);
$dept_name = $stmt->fetchColumn();
if (!$dept_name) {
    echo '<div class="alert alert-danger">Département non trouvé.</div>';
    exit;
}

// Fetch periods
$periodes = $pdo->query("SELECT * FROM periodes ORDER BY date_debut LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);

// Fetch initial solde_debut for Period 1 from soldes table
$stmt = $pdo->prepare("SELECT solde_debut FROM soldes WHERE periode_id = 1 AND departement_id = ? LIMIT 1");
$stmt->execute([$dept_id]);
$initial_solde_debut = $stmt->fetchColumn() ?: 0;

// Fetch transactions and previsions
$transactions_data = [];
$previsions_data = [];
foreach ($periodes as $periode) {
    $periode_id = $periode['id'];

    // Transactions
    $stmt = $pdo->prepare("
        SELECT 
            t.categorie_id, 
            c.type, 
            c.nature,
            SUM(t.montant) AS montant
        FROM transactions t
        JOIN categories c ON t.categorie_id = c.id
        WHERE t.departement_id = ? AND t.periode_id = ?
        GROUP BY t.categorie_id
    ");
    $stmt->execute([$dept_id, $periode_id]);
    $transactions_data[$periode_id] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Previsions
    $stmt = $pdo->prepare("
        SELECT 
            p.categorie_id, 
            c.type, 
            c.nature,
            SUM(p.montant) AS montant
        FROM previsions p
        JOIN categories c ON p.categorie_id = c.id
        WHERE p.departement_id = ? AND p.periode_id = ?
        GROUP BY p.categorie_id
    ");
    $stmt->execute([$dept_id, $periode_id]);
    $previsions_data[$periode_id] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate soldes
$soldes = [];
$previous_solde_fin = $initial_solde_debut;
foreach ($periodes as $index => $periode) {
    $periode_id = $periode['id'];
    $soldes[$periode_id]['solde_debut'] = $index === 0 ? $initial_solde_debut : $previous_solde_fin;

    $recettes_total = 0;
    $depenses_total = 0;
    foreach ($transactions_data[$periode_id] as $trans) {
        if ($trans['type'] === 'Recette') {
            $recettes_total += $trans['montant'];
        } else {
            $depenses_total += $trans['montant'];
        }
    }

    $soldes[$periode_id]['solde_fin'] = $soldes[$periode_id]['solde_debut'] + $recettes_total - $depenses_total;
    $previous_solde_fin = $soldes[$periode_id]['solde_fin'];
}
?>

<div class="table-responsive">
    <h3>Budget du département : <?= htmlspecialchars($dept_name) ?></h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Rubrique</th>
                <?php foreach ($periodes as $periode): ?>
                    <th colspan="3"><?= htmlspecialchars($periode['nom']) ?></th>
                <?php endforeach; ?>
            </tr>
            <tr>
                <th></th>
                <?php foreach ($periodes as $periode): ?>
                    <th>Prévision</th>
                    <th>Réalisation</th>
                    <th>Écart</th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <!-- Solde Début -->
            <tr>
                <td>Solde Début</td>
                <?php foreach ($periodes as $periode): ?>
                    <td><?= number_format($soldes[$periode['id']]['solde_debut'], 2, ',', ' ') ?></td>
                    <td><?= number_format($soldes[$periode['id']]['solde_debut'], 2, ',', ' ') ?></td>
                    <td>-</td>
                <?php endforeach; ?>
            </tr>

            <!-- Recettes -->
            <?php
            $categories = $pdo->query("SELECT id, type, nature FROM categories WHERE type = 'Recette' ORDER BY nature")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($categories as $cat): ?>
                <?php
                $has_transactions = false;
                foreach ($periodes as $periode) {
                    foreach ($transactions_data[$periode['id']] as $trans) {
                        if ($trans['categorie_id'] == $cat['id'] && $trans['montant'] > 0) {
                            $has_transactions = true;
                            break 2;
                        }
                    }
                }
                if ($has_transactions): ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['nature']) ?></td>
                        <?php foreach ($periodes as $periode): ?>
                            <?php
                            $periode_id = $periode['id'];
                            $prev_value = 0;
                            $real_value = 0;
                            foreach ($previsions_data[$periode_id] as $prev) {
                                if ($prev['categorie_id'] == $cat['id']) {
                                    $prev_value = $prev['montant'];
                                    break;
                                }
                            }
                            foreach ($transactions_data[$periode_id] as $trans) {
                                if ($trans['categorie_id'] == $cat['id']) {
                                    $real_value = $trans['montant'];
                                    break;
                                }
                            }
                            ?>
                            <td><?= number_format($prev_value, 2, ',', ' ') ?></td>
                            <td><?= number_format($real_value, 2, ',', ' ') ?></td>
                            <td><?= number_format($real_value - $prev_value, 2, ',', ' ') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Total Recettes -->
            <tr class="total-row">
                <td>Total Recettes</td>
                <?php foreach ($periodes as $periode): ?>
                    <?php
                    $periode_id = $periode['id'];
                    $prev_total = array_sum(array_column(
                        array_filter($previsions_data[$periode_id], function($p) { return $p['type'] === 'Recette'; }),
                        'montant'
                    ));
                    $real_total = array_sum(array_column(
                        array_filter($transactions_data[$periode_id], function($t) { return $t['type'] === 'Recette'; }),
                        'montant'
                    ));
                    ?>
                    <td><?= number_format($prev_total, 2, ',', ' ') ?></td>
                    <td><?= number_format($real_total, 2, ',', ' ') ?></td>
                    <td><?= number_format($real_total - $prev_total, 2, ',', ' ') ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- Dépenses -->
            <?php
            $categories = $pdo->query("SELECT id, type, nature FROM categories WHERE type = 'Depense' ORDER BY nature")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($categories as $cat): ?>
                <?php
                $has_transactions = false;
                foreach ($periodes as $periode) {
                    foreach ($transactions_data[$periode['id']] as $trans) {
                        if ($trans['categorie_id'] == $cat['id'] && $trans['montant'] > 0) {
                            $has_transactions = true;
                            break 2;
                        }
                    }
                }
                if ($has_transactions): ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['nature']) ?></td>
                        <?php foreach ($periodes as $periode): ?>
                            <?php
                            $periode_id = $periode['id'];
                            $prev_value = 0;
                            $real_value = 0;
                            foreach ($previsions_data[$periode_id] as $prev) {
                                if ($prev['categorie_id'] == $cat['id']) {
                                    $prev_value = $prev['montant'];
                                    break;
                                }
                            }
                            foreach ($transactions_data[$periode_id] as $trans) {
                                if ($trans['categorie_id'] == $cat['id']) {
                                    $real_value = $trans['montant'];
                                    break;
                                }
                            }
                            ?>
                            <td><?= number_format($prev_value, 2, ',', ' ') ?></td>
                            <td><?= number_format($real_value, 2, ',', ' ') ?></td>
                            <td><?= number_format($real_value - $prev_value, 2, ',', ' ') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Total Dépenses -->
            <tr class="total-row">
                <td>Total Dépenses</td>
                <?php foreach ($periodes as $periode): ?>
                    <?php
                    $periode_id = $periode['id'];
                    $prev_total = array_sum(array_column(
                        array_filter($previsions_data[$periode_id], function($p) { return $p['type'] === 'Depense'; }),
                        'montant'
                    ));
                    $real_total = array_sum(array_column(
                        array_filter($transactions_data[$periode_id], function($t) { return $t['type'] === 'Depense'; }),
                        'montant'
                    ));
                    ?>
                    <td><?= number_format($prev_total, 2, ',', ' ') ?></td>
                    <td><?= number_format($real_total, 2, ',', ' ') ?></td>
                    <td><?= number_format($real_total - $prev_total, 2, ',', ' ') ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- Solde Fin -->
            <tr>
                <td>Solde Fin</td>
                <?php foreach ($periodes as $periode): ?>
                    <td><?= number_format($soldes[$periode['id']]['solde_fin'], 2, ',', ' ') ?></td>
                    <td><?= number_format($soldes[$periode['id']]['solde_fin'], 2, ',', ' ') ?></td>
                    <td>-</td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>
</div>

<style>
    .total-row { font-weight: bold; background-color: #f8f9fa; }
    th, td { text-align: right; }
    th:first-child, td:first-child { text-align: left; }
</style>