<!-- index.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Budgetaire</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .content-section { display: none; }
        .content-section.active { display: block; }
    </style>
    
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Gestion Budgetaire</h1>
        
        <!-- Navigation -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="#" data-section="previsions">Prévisions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-section="transactions">Transactions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-section="budget-global">Budget Global</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-section="budget-dept">Budget par Département</a>
            </li>
        </ul>

        <!-- Database Connection -->
        <?php
    $host = getenv('DB_HOST');
    $dbname = "budgetITU";
    $user = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        ?>

        <!-- Prévisions Section -->
        <div id="previsions" class="content-section active">
            <h2>Visualiser/Modifier Prévisions</h2>
            <form method="POST" action="process.php" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="departement_id" class="form-select" required>
                            <?php
                            $depts = $pdo->query("SELECT * FROM departements")->fetchAll();
                            foreach ($depts as $dept) {
                                echo "<option value='{$dept['id']}'>{$dept['nom']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="periode_id" class="form-select" required>
                            <?php
                            $periodes = $pdo->query("SELECT * FROM periodes")->fetchAll();
                            foreach ($periodes as $periode) {
                                echo "<option value='{$periode['id']}'>{$periode['nom']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="montant" class="form-control" placeholder="Montant" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" name="update_prevision" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Transactions Section -->
        <div id="transactions" class="content-section">
            <h2>Insertion Transaction</h2>
            <form method="POST" action="process.php" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="departement_id" class="form-select" required>
                            <?php foreach ($depts as $dept) {
                                echo "<option value='{$dept['id']}'>{$dept['nom']}</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="categorie_id" class="form-select" required>
                            <?php
                            $cats = $pdo->query("SELECT * FROM categories")->fetchAll();
                            foreach ($cats as $cat) {
                                echo "<option value='{$cat['id']}'>{$cat['type']} - {$cat['nature']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="periode_id" class="form-select" required>
                            <?php foreach ($periodes as $periode) {
                                echo "<option value='{$periode['id']}'>{$periode['nom']}</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="montant" class="form-control" placeholder="Montant" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="add_transaction" class="btn btn-primary">Ajouter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Budget Global Section -->
        <div id="budget-global" class="content-section">
            <h2>Budget Global</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Rubrique</th>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <th colspan="3">Période <?=$i?></th>
                            <?php endfor; ?>
                        </tr>
                        <tr>
                            <th></th>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <th>Prévision</th>
                                <th>Réalisation</th>
                                <th>Écart</th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data would be populated dynamically from database -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Budget par Département Section -->
        <div id="budget-dept" class="content-section">
            <h2>Budget par Département</h2>
            <select id="dept-select" class="form-select mb-3" style="width: 200px;">
                <?php foreach ($depts as $dept) {
                    echo "<option value='{$dept['id']}'>{$dept['nom']}</option>";
                } ?>
            </select>
            <div id="dept-budget-details">
                <!-- Department budget details will be loaded via AJAX -->
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab switching
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.content-section').forEach(section => {
                    section.classList.remove('active');
                });
                document.getElementById(this.dataset.section).classList.add('active');
            });
        });

        // Department budget AJAX loading
        document.getElementById('dept-select').addEventListener('change', function() {
            fetch(`process.php?dept_id=${this.value}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('dept-budget-details').innerHTML = data;
                });
        });
    </script>
</body>
</html>