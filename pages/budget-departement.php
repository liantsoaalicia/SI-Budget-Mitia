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

// Fetch departments
$depts = $pdo->query("SELECT * FROM departements ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="container-fluid py-4">
        <div id="budget-dept" class="content-section">
            <h2>Budget par Département</h2>
            <select id="dept-select" class="form-select mb-3" style="width: 300px;">
                <option value="">Sélectionnez un département</option>
                <?php foreach ($depts as $dept): ?>
                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['nom']) ?></option>
                <?php endforeach; ?>
            </select>
            <div id="dept-budget-details"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dept-select').change(function() {
                var deptId = $(this).val();
                if (deptId) {
                    $.ajax({
                        url: 'get-dept-budget.php',
                        type: 'POST',
                        data: { dept_id: deptId },
                        success: function(response) {
                            $('#dept-budget-details').html(response);
                        },
                        error: function() {
                            $('#dept-budget-details').html('<div class="alert alert-danger">Erreur lors du chargement des données.</div>');
                        }
                    });
                } else {
                    $('#dept-budget-details').html('');
                }
            });
        });
    </script>
