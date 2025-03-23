<?php
// pages/list-departements.php
// This file displays the list of departments

require_once '../config.php';

try {
    $stmt = $pdo->query("SELECT * FROM departements ORDER BY nom");
    $departements = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des départements: " . $e->getMessage();
}
?>

<div class="container">
    <h2>Liste des départements</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Date de création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($departements) > 0): ?>
                        <?php foreach ($departements as $departement): ?>
                            <tr>
                                <td><?php echo $departement['id']; ?></td>
                                <td><?php echo $departement['nom']; ?></td>
                                <td><?php echo $departement['date_creation']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-btn" data-id="<?php echo $departement['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $departement['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Aucun département trouvé</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- Edit Department Modal -->
    <div class="modal fade" id="editDepartementModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le département</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-departement-form">
                        <input type="hidden" id="edit-id" name="id">
                        <div class="mb-3">
                            <label for="edit-nom" class="form-label">Nom du département</label>
                            <input type="text" class="form-control" id="edit-nom" name="nom" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="save-edit">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteDepartementModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation de suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer ce département? Cette action est irréversible.</p>
                    <input type="hidden" id="delete-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit department button click
    $('.edit-btn').click(function() {
        const id = $(this).data('id');
        
        // Fetch department data
        $.ajax({
            url: 'api/get_departement.php',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#edit-id').val(response.data.id);
                    $('#edit-nom').val(response.data.nom);
                    
                    // Show modal
                    new bootstrap.Modal(document.getElementById('editDepartementModal')).show();
                } else {
                    alert(response.message);
                }
            }
        });
    });
    
    // Save edit button click
    $('#save-edit').click(function() {
        const id = $('#edit-id').val();
        const nom = $('#edit-nom').val();
        
        $.ajax({
            url: 'api/update_departement.php',
            type: 'POST',
            data: { id: id, nom: nom },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('editDepartementModal')).hide();
                    
                    // Reload page to see updated data
                    loadPageContent('list-departements');
                } else {
                    alert(response.message);
                }
            }
        });
    });
    
    // Delete department button click
    $('.delete-btn').click(function() {
        const id = $(this).data('id');
        $('#delete-id').val(id);
        
        // Show confirmation modal
        new bootstrap.Modal(document.getElementById('deleteDepartementModal')).show();
    });
    
    // Confirm delete button click
    $('#confirm-delete').click(function() {
        const id = $('#delete-id').val();
        
        $.ajax({
            url: 'api/delete_departement.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('deleteDepartementModal')).hide();
                    
                    // Reload page to see updated data
                    loadPageContent('list-departements');
                } else {
                    alert(response.message);
                }
            }
        });
    });
});
</script>