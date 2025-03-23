<?php
// api/get_transaction.php
// Fetches a single transaction by ID with all details

require_once '../config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        $query = "
            SELECT t.id, t.montant, t.date_transaction, 
                   d.nom AS departement, 
                   c.type, c.nature, c.charge_type, c.source, c.destination,
                   p.nom AS periode
            FROM transactions t
            JOIN departements d ON t.departement_id = d.id
            JOIN categories c ON t.categorie_id = c.id
            JOIN periodes p ON t.periode_id = p.id
            WHERE t.id = ?
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $transaction = $stmt->fetch();
        
        if ($transaction) {
            json_response(true, '', $transaction);
        } else {
            json_response(false, 'Transaction non trouvée');
        }
    } catch (PDOException $e) {
        json_response(false, 'Erreur: ' . $e->getMessage());
    }
} else {
    json_response(false, 'ID non spécifié');
}
?>

