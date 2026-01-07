<?php
require 'db.php';

// --- CERINȚA 5: RAPORT CLIENȚI ACHITAȚI INTEGRAL ---
$sql_achitati = "SELECT c.nume, c.prenume, a.serviciu, a.data_achizitie, a.pret 
                 FROM clienti c 
                 JOIN abonamente a ON c.CNP = a.CNP 
                 WHERE a.suma_incasata = a.pret";
$res_achitati = $pdo->query($sql_achitati)->fetchAll();

// --- CERINȚA 6: RAPORT DETALIAT ORDONAT ---
$sql_detaliat = "SELECT c.nume, c.prenume, a.serviciu, a.data_achizitie, 
                        a.pret, a.suma_incasata,
                        get_rest_plata(c.CNP, a.serviciu) AS rest_plata, -- AICI FOLOSIM FUNCȚIA cerinta 8
                        CASE 
                            WHEN a.suma_incasata = a.pret THEN 'ACHITAT'
                            ELSE 'RESTANT'
                        END AS status_plata
                 FROM clienti c 
                 JOIN abonamente a ON c.CNP = a.CNP 
                ORDER BY c.nume ASC, c.prenume ASC, a.data_achizitie ASC, rest_plata DESC";
$res_detaliat = $pdo->query($sql_detaliat)->fetchAll();

// --- CERINȚA 10: CLIENTUL CU CELE MAI MULTE DATORII + PROCENT ---
$sql_datornic = "SELECT c.nume, c.prenume, c.CNP, 
                 COUNT(a.id) as nr_neachitate,
                 (SUM(a.suma_incasata) / SUM(a.pret) * 100) as procent_platit
                 FROM clienti c 
                 JOIN abonamente a ON c.CNP = a.CNP 
                 WHERE a.suma_incasata < a.pret 
                 GROUP BY c.CNP 
                 ORDER BY nr_neachitate DESC 
                 LIMIT 1";
$datornic = $pdo->query($sql_datornic)->fetch();

// --- CERINȚA 9: INTEROGAREA COMPLEXĂ (Fideli/VIP) ---
$sql_complex = "
    SELECT c.nume, c.prenume, COUNT(a.id) as total_abonamente, SUM(a.pret) as valoare_totala,
           TIMESTAMPDIFF(YEAR, MIN(a.data_achizitie), MAX(a.data_achizitie)) as ani_activitate
    FROM clienti c
    JOIN abonamente a ON c.CNP = a.CNP
    GROUP BY c.CNP
    HAVING SUM(a.pret - a.suma_incasata) = 0 
    AND (
        COUNT(a.id) >= 4 
        OR 
        (
            -- Calculam suma doar pentru abonamentele facute in primii 2 ani de la prima achizitie
            SUM(CASE 
                WHEN a.data_achizitie <= DATE_ADD((SELECT MIN(data_achizitie) FROM abonamente WHERE CNP = c.CNP), INTERVAL 2 YEAR) 
                THEN a.pret ELSE 0 END) > 1000
        )
    )
";
$res_complex = $pdo->query($sql_complex)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Rapoarte Manageriale Fitness</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Fitness System</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="clienti.php">Clienți</a></li>
                    <li class="nav-item"><a class="nav-link" href="abonamente.php">Abonamente</a></li>
                    <li class="nav-item"><a class="nav-link active" href="rapoarte.php">Rapoarte</a></li>
                </ul>
            </div>
        </div>
    </nav>

<div class="container-fluid py-4">
    <h1 class="h2 mb-4"><i class="bi bi-bar-chart-line"></i> Panou Rapoarte</h1>

    <div class="mb-5">
        <h5 class="text-danger"><span class="badge bg-danger">Cerința 10</span> Clientul cu cele mai multe abonamente neachitate</h5>
        <?php if ($datornic): ?>
            <div class="alert alert-danger shadow-sm border-start border-5 border-danger" role="alert">
                <p class="mb-1"><strong>Client:</strong> <?= htmlspecialchars($datornic['nume'] . " " . $datornic['prenume']) ?> | <strong>CNP:</strong> <?= htmlspecialchars($datornic['CNP']) ?></p>
                <hr>
                <p class="mb-0">Abonamente neachitate integral: <strong><?= $datornic['nr_neachitate'] ?></strong> | Procent plătit din total: <strong><?= number_format($datornic['procent_platit'], 2) ?>%</strong></p>
            </div>
        <?php else: ?>
            <div class="alert alert-success">Nu există restanțieri.</div>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-warning mb-5">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="card-title mb-0"><span class="badge bg-warning text-dark">Cerința 9</span> Clienți VIP / Fideli (Peste 1000 RON sau 4+ servicii)</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nume Client</th>
                        <th class="text-center">Total Abonamente</th>
                        <th class="text-end">Valoare Totală</th>
                        <th class="text-center">Interval Activitate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($res_complex)): ?>
                        <tr><td colspan="4" class="text-center py-3">Niciun client VIP identificat.</td></tr>
                    <?php else: ?>
                        <?php foreach ($res_complex as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['nume'] . " " . $r['prenume']) ?></strong></td>
                            <td class="text-center"><?= $r['total_abonamente'] ?></td>
                            <td class="text-end text-success"><?= number_format($r['valoare_totala'], 2) ?> RON</td>
                            <td class="text-center"><?= $r['ani_activitate'] + 1 ?> an(i)</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><span class="badge bg-light text-dark">Cerința 6</span> Raport Detaliat (Ordonat Nume/Data/Rest)</h5>
                </div>
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-striped table-sm">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Client</th>
                                <th>Serviciu</th>
                                <th>Data</th>
                                <th class="text-end">Rest Plată</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($res_detaliat as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['nume'] . " " . $r['prenume']) ?></td>
                                <td><?= htmlspecialchars($r['serviciu']) ?></td>
                                <td><?= $r['data_achizitie'] ?></td>
                                <td class="text-end fw-bold <?= $r['rest_plata'] > 0 ? 'text-danger' : 'text-success' ?>"><?= number_format($r['rest_plata'], 2) ?></td>
                                <td><span class="badge bg-<?= $r['status_plata'] == 'ACHITAT' ? 'success' : 'warning' ?>"><?= $r['status_plata'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><span class="badge bg-light text-dark">Cerința 5</span> Clienți cu abonamente achitate integral</h5>
                </div>
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Serviciu</th>
                                <th class="text-end">Suma</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($res_achitati as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['nume'] . " " . $r['prenume']) ?></td>
                                <td><?= htmlspecialchars($r['serviciu']) ?></td>
                                <td class="text-end text-primary"><?= number_format($r['pret'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>