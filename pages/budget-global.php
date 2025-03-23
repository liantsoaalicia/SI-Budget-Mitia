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

// Fetch base data
$periodes = $pdo->query("SELECT * FROM periodes ORDER BY date_debut LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
$departements = $pdo->query("SELECT * FROM departements ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categories ORDER BY nature")->fetchAll(PDO::FETCH_ASSOC);

// Fetch initial solde_debut for Period 1 from soldes table (assuming department_id = 0 for global)
$stmt = $pdo->prepare("SELECT solde_debut FROM soldes WHERE periode_id = 1 LIMIT 1");
$stmt->execute();
$initial_solde_debut = $stmt->fetchColumn() ?: 0; // Default to 0 if not found

// Fetch transactions and previsions
$transactions_data = [];
$previsions_data = [];
foreach ($periodes as $periode) {
    $periode_id = $periode['id'];
    
    // Transactions
    $stmt = $pdo->prepare("
        SELECT 
            t.departement_id, 
            t.categorie_id, 
            c.type, 
            c.nature, 
            d.nom AS departement_nom,
            SUM(t.montant) AS montant
        FROM transactions t
        JOIN categories c ON t.categorie_id = c.id
        JOIN departements d ON t.departement_id = d.id
        WHERE t.periode_id = :periode_id
        GROUP BY t.departement_id, t.categorie_id
    ");
    $stmt->execute(['periode_id' => $periode_id]);
    $transactions_data[$periode_id] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Previsions
    $stmt = $pdo->prepare("
        SELECT 
            p.departement_id, 
            p.categorie_id, 
            c.type, 
            c.nature, 
            SUM(p.montant) AS montant
        FROM previsions p
        JOIN categories c ON p.categorie_id = c.id
        WHERE p.periode_id = :periode_id
        GROUP BY p.departement_id, p.categorie_id
    ");
    $stmt->execute(['periode_id' => $periode_id]);
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


    <div class="container-fluid py-4">
        <div id="budget-global" class="content-section">
            <h2>Budget Global</h2>

            <button class="btn-primary" id="export-pdf-btn">Exporter PDF</button>
            <div class="table-responsive">
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
                        <?php foreach ($departements as $dept): ?>
                            <?php foreach ($categories as $cat): ?>
                                <?php if ($cat['type'] === 'Recette'): ?>
                                    <?php
                                    $has_transactions = false;
                                    foreach ($periodes as $periode) {
                                        foreach ($transactions_data[$periode['id']] as $trans) {
                                            if ($trans['departement_id'] == $dept['id'] && $trans['categorie_id'] == $cat['id'] && $trans['montant'] > 0) {
                                                $has_transactions = true;
                                                break 2;
                                            }
                                        }
                                    }
                                    if ($has_transactions): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($dept['nom'] . " - " . $cat['nature']) ?></td>
                                            <?php foreach ($periodes as $periode): ?>
                                                <?php
                                                $periode_id = $periode['id'];
                                                $prev_value = 0;
                                                $real_value = 0;
                                                foreach ($previsions_data[$periode_id] as $prev) {
                                                    if ($prev['departement_id'] == $dept['id'] && $prev['categorie_id'] == $cat['id']) {
                                                        $prev_value = $prev['montant'];
                                                        break;
                                                    }
                                                }
                                                foreach ($transactions_data[$periode_id] as $trans) {
                                                    if ($trans['departement_id'] == $dept['id'] && $trans['categorie_id'] == $cat['id']) {
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
                                <?php endif; ?>
                            <?php endforeach; ?>
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
                        <?php foreach ($departements as $dept): ?>
                            <?php foreach ($categories as $cat): ?>
                                <?php if ($cat['type'] === 'Depense'): ?>
                                    <?php
                                    $has_transactions = false;
                                    foreach ($periodes as $periode) {
                                        foreach ($transactions_data[$periode['id']] as $trans) {
                                            if ($trans['departement_id'] == $dept['id'] && $trans['categorie_id'] == $cat['id'] && $trans['montant'] > 0) {
                                                $has_transactions = true;
                                                break 2;
                                            }
                                        }
                                    }
                                    if ($has_transactions): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($dept['nom'] . " - " . $cat['nature']) ?></td>
                                            <?php foreach ($periodes as $periode): ?>
                                                <?php
                                                $periode_id = $periode['id'];
                                                $prev_value = 0;
                                                $real_value = 0;
                                                foreach ($previsions_data[$periode_id] as $prev) {
                                                    if ($prev['departement_id'] == $dept['id'] && $prev['categorie_id'] == $cat['id']) {
                                                        $prev_value = $prev['montant'];
                                                        break;
                                                    }
                                                }
                                                foreach ($transactions_data[$periode_id] as $trans) {
                                                    if ($trans['departement_id'] == $dept['id'] && $trans['categorie_id'] == $cat['id']) {
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
                                <?php endif; ?>
                            <?php endforeach; ?>
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
        </div>
    </div>
