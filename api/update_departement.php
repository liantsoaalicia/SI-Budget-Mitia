<?php
// api/update_departement.php
// Updates a department

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['nom'])) {
    $id = (int)$_POST['id'];
    $nom = sanitize($_POST['nom']);
    
    if (empty($nom)) {
        json_response(false, 'Le nom du département est requis');
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE departements SET nom = ? WHERE id = ?");
            $stmt->execute([$nom, $id]);
            
            if ($stmt->rowCount() > 0) {
                json_response(true, 'Département mis à jour avec succès');
            } else {
                json_response(true, 'Aucune modification apportée');
            }
        } catch (PDOException $e) {
            json_response(false, 'Erreur: ' . $e->getMessage());
        }
    }
} else {
    json_response(false, 'Données manquantes');
}
?>