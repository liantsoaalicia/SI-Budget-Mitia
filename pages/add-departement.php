<?php
// pages/add-departement.php
// This file handles adding a new department

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_departement'])) {
    require_once '../config.php';
    
    $nom = sanitize($_POST['nom']);
    
    if (empty($nom)) {
        $error = "Le nom du département est requis.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO departements (nom) VALUES (?)");
            $stmt->execute([$nom]);
            
            $success = "Département ajouté avec succès!";
            
            // If AJAX request, return JSON response
            if (is_ajax_request()) {
                json_response(true, $success);
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout du département: " . $e->getMessage();
            
            // If AJAX request, return JSON response
            if (is_ajax_request()) {
                json_response(false, $error);
            }
        }
    }
}
?>

<div class="container">
    <h2>Ajouter un département</h2>
    
    <div id="message-container"></div>
    
    <form id="add-departement-form" method="post">
        <div class="mb-3">
            <label for="nom" class="form-label">Nom du département</label>
            <input type="text" class="form-control" id="nom" name="nom" required>
        </div>
        
        <button type="submit" name="add_departement" class="btn btn-primary">Ajouter</button>
    </form>
</div>

<script>
$(document).ready(function() {
    // Submit form via AJAX
    $('#add-departement-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'pages/add-departement.php',
            type: 'POST',
            data: $(this).serialize() + '&add_departement=1',
            dataType: 'json',
            success: function(response) {
                let alertClass = response.success ? 'alert-success' : 'alert-danger';
                $('#message-container').html(`
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${response.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
                
                if (response.success) {
                    $('#add-departement-form')[0].reset();
                }
            },
            error: function() {
                $('#message-container').html(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Une erreur est survenue lors de la communication avec le serveur.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
            }
        });
    });
});
</script>

