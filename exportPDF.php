<?php
require('fpdf.php'); // Assurez-vous que fpdf.php est dans le même répertoire

// Database Connection
 $host = 'localhost';
 $dbname = 'budgetITU';
 $username = 'root';
 $password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fetch data
$periodes = $pdo->query("SELECT * FROM periodes ORDER BY date_debut LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
$departements = $pdo->query("SELECT * FROM departements ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categories ORDER BY nature")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT solde_debut FROM soldes WHERE periode_id = 1 LIMIT 1");
$stmt->execute();
$initial_solde_debut = $stmt->fetchColumn() ?: 0;

$transactions_data = [];
$previsions_data = [];
foreach ($periodes as $periode) {
    $periode_id = $periode['id'];
    $stmt = $pdo->prepare("
        SELECT t.departement_id, t.categorie_id, c.type, c.nature, d.nom AS departement_nom, SUM(t.montant) AS montant
        FROM transactions t
        JOIN categories c ON t.categorie_id = c.id
        JOIN departements d ON t.departement_id = d.id
        WHERE t.periode_id = :periode_id
        GROUP BY t.departement_id, t.categorie_id
    ");
    $stmt->execute(['periode_id' => $periode_id]);
    $transactions_data[$periode_id] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT p.departement_id, p.categorie_id, c.type, c.nature, SUM(p.montant) AS montant
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

// Initialisation du PDF
$pdf = new FPDF('P', 'mm', 'A4'); // Orientation portrait
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Titre général
$pdf->Cell(0, 10, 'Budget Global', 0, 1, 'C');
$pdf->Ln(10);

// Largeurs des colonnes
$col_width_rubrique = 80; // Largeur colonne Rubrique
$col_width_data = 30; // Largeur colonnes Prévision, Réalisation, Écart

// Boucle pour chaque période
foreach ($periodes as $periode) {
    $periode_id = $periode['id'];

    // Titre de la période
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, $periode['nom'], 0, 1, 'L');
    $pdf->Ln(2);

    // En-têtes du tableau
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($col_width_rubrique, 7, 'Rubrique', 1);
    $pdf->Cell($col_width_data, 7, 'Prevision', 1, 0, 'C');
    $pdf->Cell($col_width_data, 7, 'Realisation', 1, 0, 'C');
    $pdf->Cell($col_width_data, 7, 'Ecart', 1, 0, 'C');
    $pdf->Ln();

    // Corps du tableau
    $pdf->SetFont('Arial', '', 9);

    // Solde Début
    $pdf->Cell($col_width_rubrique, 6, 'Solde Debut', 1);
    $solde_debut = number_format($soldes[$periode_id]['solde_debut'], 2, ',', ' ');
    $pdf->Cell($col_width_data, 6, $solde_debut, 1, 0, 'R');
    $pdf->Cell($col_width_data, 6, $solde_debut, 1, 0, 'R');
    $pdf->Cell($col_width_data, 6, '-', 1, 0, 'C');
    $pdf->Ln();

    // Recettes
    foreach ($departements as $dept) {
        foreach ($categories as $cat) {
            if ($cat['type'] === 'Recette') {
                $has_transactions = false;
                foreach ($transactions_data[$periode_id] as $trans) {
                    if ($trans['departement_id'] == $dept['id'] && $trans['categorie_id'] == $cat['id'] && $trans['montant'] > 0) {
                        $has_transactions = true;
                        break;
                    }
                }
                if ($has_transactions) {
                    $pdf->Cell($col_width_rubrique, 6, $dept['nom'] . ' - ' . $cat['nature'], 1);
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
                    $pdf->Cell($col_width_data, 6, number_format($prev_value, 2, ',', ' '), 1, 0, 'R');
                    $pdf->Cell($col_width_data, 6, number_format($real_value, 2, ',', ' '), 1, 0, 'R');
                    $pdf->Cell($col_width_data, 6, number_format($real_value - $prev_value, 2, ',', ' '), 1, 0, 'R');
                    $pdf->Ln();
                }
            }
        }
    }

    // Total Recettes
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($col_width_rubrique, 6, 'Total Recettes', 1);
    $prev_total = array_sum(array_column(array_filter($previsions_data[$periode_id], function($p) { return $p['type'] === 'Recette'; }), 'montant'));
    $real_total = array_sum(array_column(array_filter($transactions_data[$periode_id], function($t) { return $t['type'] === 'Recette'; }), 'montant'));
    $pdf->Cell($col_width_data, 6, number_format($prev_total, 2, ',', ' '), 1, 0, 'R');
    $pdf->Cell($col_width_data, 6, number_format($real_total, 2, ',', ' '), 1, 0, 'R');
    $pdf->Cell($col_width_data, 6, number_format($real_total - $prev_total, 2, ',', ' '), 1, 0, 'R');
    $pdf->Ln();

    // Dépenses
    $pdf->SetFont('Arial', '', 9);
    foreach ($departements as $dept) {
        foreach ($categories as $cat) {
            if ($cat['type'] === 'Depense') {
                $has_transactions = false;
                foreach ($transactions_data[$periode_id] as $trans) {
                    if ($trans['departement_id'] == $dept['id'] && $trans['categorie_id'] == $cat['id'] && $trans['montant'] > 0) {
                        $has_transactions = true;
                        break;
                    }
                }
                if ($has_transactions) {
                    $pdf->Cell($col_width_rubrique, 6, $dept['nom'] . ' - ' . $cat['nature'], 1);
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
                    $pdf->Cell($col_width_data, 6, number_format($prev_value, 2, ',', ' '), 1, 0, 'R');
                    $pdf->Cell($col_width_data, 6, number_format($real_value, 2, ',', ' '), 1, 0, 'R');
                    $pdf->Cell($col_width_data, 6, number_format($real_value - $prev_value, 2, ',', ' '), 1, 0, 'R');
                    $pdf->Ln();
                }
            }
        }
    }

    // Total Dépenses
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($col_width_rubrique, 6, 'Total Depenses', 1);
    $prev_total = array_sum(array_column(array_filter($previsions_data[$periode_id], function($p) { return $p['type'] === 'Depense'; }), 'montant'));
    $real_total = array_sum(array_column(array_filter($transactions_data[$periode_id], function($t) { return $t['type'] === 'Depense'; }), 'montant'));
    $pdf->Cell($col_width_data, 6, number_format($prev_total, 2, ',', ' '), 1, 0, 'R');
    $pdf->Cell($col_width_data, 6, number_format($real_total, 2, ',', ' '), 1, 0, 'R');
    $pdf->Cell($col_width_data, 6, number_format($real_total - $prev_total, 2, ',', ' '), 1, 0, 'R');
    $pdf->Ln();

    // Solde Fin
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($col_width_rubrique, 6, 'Solde Fin', 1);
    $solde_fin = number_format($soldes[$periode_id]['solde_fin'], 2, ',', ' ');
    $pdf->Cell($col_width_data, 6, $solde_fin, 1, 0, 'R');
    $pdf->Cell($col_width_data, 6, $solde_fin, 1, 0, 'R');
    $pdf->Cell($col_width_data, 6, '-', 1, 0, 'C');
    $pdf->Ln();

    // Espacement avant le prochain tableau
    $pdf->Ln(10);

    // Vérifier si on doit ajouter une nouvelle page
    if ($pdf->GetY() > 250) { // Si on est proche du bas de la page
        $pdf->AddPage();
    }
}

// Génération du PDF
$pdf->Output('D', 'Budget_Global.pdf');
?>