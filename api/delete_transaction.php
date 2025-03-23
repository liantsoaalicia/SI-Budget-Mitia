<?php
// api/delete_transaction.php
// Deletes a transaction and updates balances

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    try {
        $pdo->beginTransaction();
        
        // Get transaction details before deleting
        $stmt = $pdo->prepare("
            SELECT t.*, c.type 
            FROM transactions t 
            JOIN categories c ON t.categorie_id = c.id 
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        $transaction = $stmt->fetch();
        
        if (!$transaction) {
            $pdo->rollBack();
            json_response(false, 'Transaction non trouvée');
            exit;
        }
        
        // Delete the transaction
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->execute([$id]);
        
        // Update solde for this department and period
        $departement_id = $transaction['departement_id'];
        $periode_id = $transaction['periode_id'];
        $montant = $transaction['montant'];
        $type = $transaction['type'];
        
        // Get current solde
        $stmt = $pdo->prepare("SELECT id, solde_fin FROM soldes WHERE departement_id = ? AND periode_id = ?");
        $stmt->execute([$departement_id, $periode_id]);
        $solde = $stmt->fetch();
        
        if ($solde) {
            // Update solde_fin (reverse the transaction effect)
            $new_solde_fin = ($type === 'Recette') 
                ? $solde['solde_fin'] - $montant 
                : $solde['solde_fin'] + $montant;
            
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
                
                // Update all subsequent periods
                $stmt = $pdo->prepare("
                    SELECT id, periode_id, solde_fin - solde_debut AS diff
                    FROM soldes 
                    WHERE departement_id = ? AND periode_id > ?
                    ORDER BY periode_id
                ");
                $stmt->execute([$departement_id, $next_periode_id]);
                $subsequent_soldes = $stmt->fetchAll();
                
                $previous_fin = $new_solde_fin;
                foreach ($subsequent_soldes as $s) {
                    $new_debut = $previous_fin;
                    $new_fin = $new_debut + $s['diff'];
                    
                    $stmt = $pdo->prepare("UPDATE soldes SET solde_debut = ?, solde_fin = ? WHERE id = ?");
                    $stmt->execute([$new_debut, $new_fin, $s['id']]);
                    
                    $previous_fin = $new_fin;
                }
            }
        }
        
        $pdo->commit();
        json_response(true, 'Transaction supprimée avec succès');
    } catch (PDOException $e) {
        $pdo->rollBack();
        json_response(false, 'Erreur: ' . $e->getMessage());
    }
} else {
    json_response(false, 'ID non spécifié');
}
?>