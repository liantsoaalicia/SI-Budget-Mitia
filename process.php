<!-- process.php -->
<?php
 $host = 'localhost';
 $dbname = 'budgetITU';
 $user = 'root';
 $password = '';

$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST['update_prevision'])) {
    $stmt = $pdo->prepare("INSERT INTO previsions (departement_id, categorie_id, periode_id, montant) 
        VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE montant = ?");
    $stmt->execute([$_POST['departement_id'], 1, $_POST['periode_id'], $_POST['montant'], $_POST['montant']]);
}

if (isset($_POST['add_transaction'])) {
    $stmt = $pdo->prepare("INSERT INTO transactions (departement_id, categorie_id, periode_id, montant) 
        VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['departement_id'], $_POST['categorie_id'], $_POST['periode_id'], $_POST['montant']]);
    
    // Update solde_fin and next period's solde_debut
    $pdo->beginTransaction();
    try {
        // Update current period's solde_fin
        $stmt = $pdo->prepare("
            UPDATE soldes s
            SET solde_fin = (
                SELECT s.solde_debut + 
                       COALESCE((SELECT SUM(montant) FROM transactions t 
                                WHERE t.departement_id = s.departement_id 
                                AND t.periode_id = s.periode_id 
                                AND EXISTS (SELECT 1 FROM categories c WHERE c.id = t.categorie_id AND c.type = 'Recette')), 0) -
                       COALESCE((SELECT SUM(montant) FROM transactions t 
                                WHERE t.departement_id = s.departement_id 
                                AND t.periode_id = s.periode_id 
                                AND EXISTS (SELECT 1 FROM categories c WHERE c.id = t.categorie_id AND c.type = 'Dépense')), 0)
            )
            WHERE s.departement_id = ? AND s.periode_id = ?
        ");
        $stmt->execute([$_POST['departement_id'], $_POST['periode_id']]);
        
        // Update next period's solde_debut
        $nextPeriode = $_POST['periode_id'] + 1;
        $stmt = $pdo->prepare("
            INSERT INTO soldes (departement_id, periode_id, solde_debut)
            SELECT ?, ?, solde_fin
            FROM soldes
            WHERE departement_id = ? AND periode_id = ?
            ON DUPLICATE KEY UPDATE solde_debut = VALUES(solde_debut)
        ");
        $stmt->execute([$_POST['departement_id'], $nextPeriode, $_POST['departement_id'], $_POST['periode_id']]);
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// AJAX request for department budget
if (isset($_GET['dept_id'])) {
    $stmt = $pdo->prepare("
        SELECT p.nom as periode, pr.montant as prevision, 
               SUM(t.montant) as realisation,
               pr.montant - COALESCE(SUM(t.montant), 0) as ecart
        FROM periodes p
        LEFT JOIN previsions pr ON pr.periode_id = p.id AND pr.departement_id = ?
        LEFT JOIN transactions t ON t.periode_id = p.id AND t.departement_id = ?
        GROUP BY p.id, p.nom, pr.montant
    ");
    $stmt->execute([$_GET['dept_id'], $_GET['dept_id']]);
    $results = $stmt->fetchAll();
    
    echo '<table class="table table-striped">
        <thead>
            <tr>
                <th>Période</th>
                <th>Prévision</th>
                <th>Réalisation</th>
                <th>Écart</th>
            </tr>
        </thead>
        <tbody>';
    foreach ($results as $row) {
        echo "<tr>
            <td>{$row['periode']}</td>
            <td>{$row['prevision']}</td>
            <td>{$row['realisation']}</td>
            <td>{$row['ecart']}</td>
        </tr>";
    }
    echo '</tbody></table>';
}
?>