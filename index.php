<?php
require('config.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Gestion de Budget</title>
    <!-- Bootstrap 5 CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .content {
            padding: 20px;
            min-height: 80vh;
        }
        .hidden {
            display: none;
        }

        .table-responsive { max-height: 600px; overflow: auto; }
        thead th { position: sticky; top: 0; background: #f8f9fa; z-index: 1; }
        .total-row { font-weight: bold; background-color: #e9ecef; }



    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Gestion Budget</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-page="home">Accueil</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="departementDropdown" role="button" data-bs-toggle="dropdown">
                            Départements
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-page="add-departement">Ajouter un département</a></li>
                            <li><a class="dropdown-item" href="#" data-page="list-departements">Liste des départements</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="departementDropdown" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-page="add-categories">Ajouter une categorie </a></li>
                            <li><a class="dropdown-item" href="#" data-page="list-categories">Liste des categories</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="transactionDropdown" role="button" data-bs-toggle="dropdown">
                            Dépenses & Recettes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-page="add-transaction">Ajouter une transaction</a></li>
                            <li><a class="dropdown-item" href="#" data-page="list-transactions">Liste des transactions</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="previsionDropdown" role="button" data-bs-toggle="dropdown">
                            Prévisions
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-page="add-prevision">Ajouter une prévision</a></li>

                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="budgetDropdown" role="button" data-bs-toggle="dropdown">
                            Budget
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-page="budget-global">Budget global</a></li>
                            <li><a class="dropdown-item" href="#" data-page="budget-departement">Budget par département</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container content">
        <!-- Home page content initially visible -->
        <div id="home" class="page-content">
            <h1>Bienvenue dans le système de gestion de budget</h1>
            <p>Utilisez le menu de navigation pour accéder aux différentes fonctionnalités du système.</p>
            
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-building"></i> Départements</h5>
                            <p class="card-text">Gérez les départements de votre organisation.</p>
                            <a href="#" class="btn btn-primary" data-page="list-departements">Voir les départements</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-exchange-alt"></i> Transactions</h5>
                            <p class="card-text">Enregistrez les dépenses et recettes par département.</p>
                            <a href="#" class="btn btn-primary" data-page="add-transaction">Nouvelle transaction</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-chart-line"></i> Budget</h5>
                            <p class="card-text">Consultez le budget global ou par département.</p>
                            <a href="#" class="btn btn-primary" data-page="budget-global">Voir le budget</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Other page contents will be loaded here dynamically -->
        <div id="add-departement" class="page-content hidden"></div>
        <div id="list-departements" class="page-content hidden"></div>
        <div id="add-categories" class="page-content hidden"></div>
        <div id="list-categories" class="page-content hidden"></div>
        <div id="add-transaction" class="page-content hidden"></div>
        <div id="list-transactions" class="page-content hidden"></div>
        <div id="add-prevision" class="page-content hidden"></div>
        <div id="view-previsions" class="page-content hidden"></div>
        <div id="budget-global" class="page-content hidden"></div>
        <div id="budget-departement" class="page-content hidden"></div>
    </div>

    <footer class="bg-light text-center p-3">
        <div class="container">
            <p class="mb-0">Système de Gestion de Budget &copy; 2025</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    
    <script>
        $(document).ready(function() {
    // Navigation click handler
    $('[data-page]').click(function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        
        // Hide all page contents
        $('.page-content').addClass('hidden');
        
        // Show the selected page
        $('#' + page).removeClass('hidden');
        
        // Load content if not already loaded
        if ($('#' + page).is(':empty')) {
            loadPageContent(page);
        }
    });
    
    // Function to load page content via AJAX
    function loadPageContent(page) {
        $.ajax({
            url: 'pages/' + page + '.php',
            type: 'GET',
            success: function(data) {
                $('#' + page).html(data);
            },
            error: function() {
                $('#' + page).html('<div class="alert alert-danger">Erreur de chargement de la page</div>');
            }
        });
    }

    // Gestion du clic sur le bouton Exporter PDF
    $('#export-pdf-btn').click(function(e) {
        e.preventDefault();
        // Redirige vers exportPDF.php pour déclencher le téléchargement
        window.location.href = 'exportPDF.php';
    });
});
    </script>
</body>
</html>