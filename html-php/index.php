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
    <link rel="stylesheet" href="style.css"> <style>
        /* Stiluri specifice doar pentru dashboard */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 5px solid #3498db;
        }
        .card h3 { border: none; color: #7f8c8d; font-size: 0.9em; text-transform: uppercase; }
        .card .number { font-size: 2.5em; font-weight: bold; color: #2c3e50; margin: 10px 0; }
        .btn-link {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-link:hover { background-color: #2980b9; }
    </style>
</head>
<body>

    <div class="nav">
        <a href="index.php">Acasă</a>
        <a href="clienti.php">Clienți</a>
        <a href="abonamente.php">Abonamente & Plăți</a>
        <a href="rapoarte.php">Rapoarte</a>
    </div>

    <h1>Panou de Control (Dashboard)</h1>
    <p>Bine ai venit în sistemul de evidență al centrului de fitness.</p>

    <div class="dashboard-grid">
        <div class="card">
            <h3>Total Clienți</h3>
            <div class="number"><?= $count_clienti ?></div>
            <a href="clienti.php" class="btn-link">Gestionează Clienți</a>
        </div>

        <div class="card" style="border-top-color: #27ae60;">
            <h3>Abonamente Emise</h3>
            <div class="number"><?= $count_abonamente ?></div>
            <a href="abonamente.php" class="btn-link">Înregistrează Plată</a>
        </div>

        <div class="card" style="border-top-color: #f39c12;">
            <h3>Total Încasări</h3>
            <div class="number"><?= number_format($total_incasari, 2) ?> <small>RON</small></div>
            <a href="rapoarte.php" class="btn-link">Vezi Analiză</a>
        </div>
    </div>

    <div style="margin-top: 40px;">
        <h2>Scurtături Tehnice</h2>
        <ul>
            <li>Baza de date rulează pe: <strong>MySQL (InnoDB)</strong></li>
            <li>Conexiune: <strong>PDO (Secure)</strong></li>
            <li>Logică Business: <strong>Triggere SQL active</strong></li>
        </ul>
    </div>

</body>
</html>