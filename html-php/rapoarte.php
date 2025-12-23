<?php
require 'db.php';

// --- 1. RAPORT CLIENȚI ACHITAȚI INTEGRAL (Req 5) ---
// Selectăm clienții care au DOAR abonamente achitate (sau cei care au cel puțin unul achitat?
// Cerința zice "clienți cu abonamente achitate". Interpretăm: lista acelor abonamente sau clienții care nu au datorii.
// Voi afișa clienții și abonamentele lor care sunt "curate".
$sql_achitati = "SELECT c.nume, c.prenume, a.serviciu, a.data_achizitie, a.pret 
                 FROM clienti c 
                 JOIN abonamente a ON c.CNP = a.CNP 
                 WHERE a.suma_incasata = a.pret";
$res_achitati = $pdo->query($sql_achitati)->fetchAll();

// --- 2. RAPORT DETALIAT ORDONAT (Req 6) ---
// Nume, prenume, data asc, rest de plata desc
$sql_detaliat = "SELECT c.nume, c.prenume, a.data_achizitie, a.pret, 
                 (a.pret - a.suma_incasata) AS rest_plata 
                 FROM clienti c 
                 JOIN abonamente a ON c.CNP = a.CNP 
                 ORDER BY c.nume ASC, c.prenume ASC, a.data_achizitie ASC, rest_plata DESC";
$res_detaliat = $pdo->query($sql_detaliat)->fetchAll();

// --- 3. CLIENTUL CU CELE MAI MULTE DATORII (Req 10) ---
// Numaram cate abonamente au rest > 0
$sql_datornic = "SELECT c.nume, c.prenume, c.CNP, COUNT(a.id) as nr_neachitate 
                 FROM clienti c 
                 JOIN abonamente a ON c.CNP = a.CNP 
                 WHERE a.suma_incasata < a.pret 
                 GROUP BY c.CNP 
                 ORDER BY nr_neachitate DESC 
                 LIMIT 1";
$datornic = $pdo->query($sql_datornic)->fetch();

// --- 4. INTEROGAREA COMPLEXĂ (Req 9) ---
// "Minim 4 achitate integral SAU (Total > 1000 in max 2 ani), FARA datorii"
// Aceasta e "Monstrul" SQL.
$sql_complex = "
    SELECT c.nume, c.prenume, 
           COUNT(a.id) as total_abonamente, 
           SUM(a.pret) as valoare_totala,
           DATEDIFF(MAX(a.data_achizitie), MIN(a.data_achizitie)) as zile_activitate
    FROM clienti c
    JOIN abonamente a ON c.CNP = a.CNP
    GROUP BY c.CNP
    HAVING 
        -- Conditia Exclusiva: Sa nu aiba datorii (suma resturilor sa fie 0)
        SUM(a.pret - a.suma_incasata) = 0 
        AND (
            -- Conditia A: Minim 4 achitate (care sunt toate, ca n-are datorii)
            COUNT(a.id) >= 4 
            OR 
            -- Conditia B: Valoare > 1000 in maxim 2 ani (730 zile)
            (SUM(a.pret) > 1000 AND DATEDIFF(MAX(a.data_achizitie), MIN(a.data_achizitie)) <= 730)
        )
";
$res_complex = $pdo->query($sql_complex)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Rapoarte Manageriale</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Evidență Abonamente</a>
        <!-- <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button> -->
        <div class="collapse navbar-collapse" id="navbarColor01">
        <ul class="navbar-nav me-auto">
            <li class="nav-item">
            <a class="nav-link" href="index.php">Home
            </a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="clienti.php">Clienți</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="abonamente.php">Abonamente</a>
            </li>
            <li class="nav-item">
            <a class="nav-link active" href="rapoarte.php">Rapoarte</a>
            <span class="visually-hidden">(current)</span>
            </li>
        </ul>
        </div>
    </div>
    </nav>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><i class="bi bi-bar-chart-line"></i> Centru de Comandă & Rapoarte</h1>
    </div>

    <?php if ($datornic): ?>
        <div class="alert alert-danger shadow-sm d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
            <div>
                <h4 class="alert-heading mb-1">Atenție: Rău Platnic Identificat</h4>
                <p class="mb-0">
                    Clientul cu cele mai multe datorii este 
                    <strong><?= $datornic['nume'] . " " . $datornic['prenume'] ?></strong> 
                    (CNP: <?= $datornic['CNP'] ?>). 
                    <br>
                    Abonamente neachitate: <span class="badge bg-danger rounded-pill"><?= $datornic['nr_neachitate'] ?></span>
                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-success shadow-sm d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill fs-3 me-3"></i>
            <div>
                <h4 class="alert-heading mb-1">Totul este în regulă!</h4>
                <p class="mb-0">Nu există clienți cu datorii critice în acest moment.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-warning mb-4">
        <div class="card-header bg-warning bg-opacity-10 border-warning">
            <h5 class="card-title text-dark mb-0"><i class="bi bi-trophy-fill text-warning"></i> Clienți VIP / Fideli</h5>
            <small class="text-muted">Criterii: 0 datorii ȘI (Minim 4 abonamente SAU >1000 RON în 2 ani)</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nume Client</th>
                            <th class="text-center">Total Abonamente</th>
                            <th class="text-end">Valoare Totală</th>
                            <th class="text-center">Durată Activitate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($res_complex as $r): ?>
                        <tr>
                            <td class="fw-bold"><?= $r['nume'] . " " . $r['prenume'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?= $r['total_abonamente'] ?></span>
                            </td>
                            <td class="text-end text-success fw-bold"><?= number_format($r['valoare_totala'], 2) ?> RON</td>
                            <td class="text-center"><?= $r['zile_activitate'] ?> zile</td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($res_complex)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Niciun client nu îndeplinește condițiile stricte.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Raport Detaliat Abonamente</h5>
                    <small class="text-muted">Ordonat după: Nume, Dată, Rest de plată (desc)</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped table-sm mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Nume</th>
                                    <th>Data</th>
                                    <th class="text-end">Preț</th>
                                    <th class="text-end">Rest Plată</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($res_detaliat as $r): ?>
                                <tr>
                                    <td><?= $r['nume'] . " " . $r['prenume'] ?></td>
                                    <td><small><?= $r['data_achizitie'] ?></small></td>
                                    <td class="text-end"><?= $r['pret'] ?></td>
                                    <td class="text-end fw-bold <?= $r['rest_plata'] > 0 ? 'text-danger' : 'text-success' ?>">
                                        <?= number_format($r['rest_plata'], 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check-all"></i> Abonamente Achitate Integral</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Nume</th>
                                    <th>Serviciu</th>
                                    <th>Data</th>
                                    <th class="text-end">Valoare</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($res_achitati as $r): ?>
                                <tr>
                                    <td><?= $r['nume'] . " " . $r['prenume'] ?></td>
                                    <td><span class="badge bg-info text-dark"><?= $r['serviciu'] ?></span></td>
                                    <td><small><?= $r['data_achizitie'] ?></small></td>
                                    <td class="text-end"><?= $r['pret'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>