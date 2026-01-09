<?php
require 'db.php';

// Statistici rapide pentru Dashboard
$count_clienti = $pdo->query("SELECT COUNT(*) FROM clienti")->fetchColumn();
$count_abonamente = $pdo->query("SELECT COUNT(*) FROM abonamente")->fetchColumn();
$total_incasari = $pdo->query("SELECT SUM(suma_incasata) FROM abonamente")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Fitness Management Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Evidență Abonamente</a>

        <div class="collapse navbar-collapse" id="navbarColor01">
        <ul class="navbar-nav me-auto">
            <li class="nav-item">
            <a class="nav-link active" href="index.php">Home
                <span class="visually-hidden">(current)</span>
            </a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="clienti.php">Clienți</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="abonamente.php">Abonamente</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="rapoarte.php">Rapoarte</a>
            </li>
        </ul>
        </div>
    </div>
    </nav>



    <div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h2">Panou de Control (Dashboard)</h1>
        <p class="text-muted">Bine ai venit în sistemul de evidență al centrului de fitness.</p>
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-4">
            <div class="card h-100 border-primary shadow-sm">
                <div class="card-header bg-transparent border-primary text-primary fw-bold">
                    Statistici
                </div>
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <h5 class="card-title text-secondary">Total Clienți</h5>
                    <p class="display-4 fw-bold text-dark my-3">
                        <?= $count_clienti ?>
                    </p>
                    <a href="clienti.php" class="btn btn-primary w-100 mt-auto">
                        Gestionează Clienți
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card h-100 border-success shadow-sm">
                <div class="card-header bg-transparent border-success text-success fw-bold">
                    Activitate
                </div>
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <h5 class="card-title text-secondary">Abonamente Emise</h5>
                    <p class="display-4 fw-bold text-dark my-3">
                        <?= $count_abonamente ?>
                    </p>
                    <a href="abonamente.php" class="btn btn-outline-success w-100 mt-auto">
                        Înregistrează Plată
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card h-100 border-warning shadow-sm">
                <div class="card-header bg-transparent border-warning text-warning fw-bold">
                    Finanțe
                </div>
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <h5 class="card-title text-secondary">Total Încasări</h5>
                    <p class="display-5 fw-bold text-dark my-3">
                        <?= number_format($total_incasari, 2) ?> <small class="fs-6 text-muted">RON</small>
                    </p>
                    <a href="rapoarte.php" class="btn btn-outline-warning text-dark w-100 mt-auto">
                        Vezi Analiză
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <div class="alert alert-light border shadow-sm">
            <h4 class="alert-heading h5"><i class="bi bi-cpu"></i> Scurtături Tehnice</h4>
            <hr>
            <ul class="list-group list-group-flush bg-transparent">
                <li class="list-group-item bg-transparent">Baza de date rulează pe: <span class="badge bg-secondary">MySQL (InnoDB)</span></li>
                <li class="list-group-item bg-transparent">Conexiune: <span class="badge bg-success">PDO (Secure)</span></li>
                <li class="list-group-item bg-transparent">Logică Business: <span class="badge bg-info text-dark">Triggere SQL active</span></li>
            </ul>
        </div>
    </div>
</div>

</body>
</html>