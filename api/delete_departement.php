
<?php
// api/delete_departement.php
// Deletes a department

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM departements WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            json_response(true, 'Département supprimé avec succès');
        } else {
            json_response(false, 'Département non trouvé');
        }
    } catch (PDOException $e) {
        json_response(false, 'Erreur: ' . $e->getMessage());
    }
} else {
    json_response(false, 'ID non spécifié');
}
?>