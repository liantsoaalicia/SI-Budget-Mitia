<?php
// api/get_departement.php
// Fetches a single department by ID

require_once '../config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM departements WHERE id = ?");
        $stmt->execute([$id]);
        $departement = $stmt->fetch();
        
        if ($departement) {
            json_response(true, '', $departement);
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