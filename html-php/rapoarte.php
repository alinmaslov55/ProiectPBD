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

    <div class="nav">
        <a href="clienti.php">Clienți</a>
        <a href="abonamente.php">Abonamente & Plăți</a>
        <a href="rapoarte.php" style="text-decoration: underline;">Rapoarte</a>
    </div>

    <h1>Centru de Comandă & Rapoarte</h1>

    <?php if ($datornic): ?>
    <div class="alert-box">
        <h3 style="margin-top:0; color: #cc0000;">⚠️ Atenție: Rău Platnic Identificat</h3>
        <p>Clientul cu cele mai multe abonamente neachitate este: 
           <strong><?= $datornic['nume'] . " " . $datornic['prenume'] ?></strong> 
           (CNP: <?= $datornic['CNP'] ?>) - 
           Nr. Datorii: <strong><?= $datornic['nr_neachitate'] ?></strong>
        </p>
    </div>
    <?php else: ?>
        <div style="color: green; padding: 10px; border: 1px solid green; margin-bottom: 20px;">Nu există rău-platnici!</div>
    <?php endif; ?>

    <h2>Clienți VIP / Fideli (Fără datorii & Activitate intensă)</h2>
    <p><em>Criterii: 0 datorii ȘI (Minim 4 abonamente SAU >1000 RON în 2 ani)</em></p>
    <table>
        <thead>
            <tr>
                <th>Nume Client</th>
                <th>Total Abonamente</th>
                <th>Valoare Totală</th>
                <th>Durată Activitate (Zile)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($res_complex as $r): ?>
            <tr>
                <td><?= $r['nume'] . " " . $r['prenume'] ?></td>
                <td><?= $r['total_abonamente'] ?></td>
                <td><?= number_format($r['valoare_totala'], 2) ?> RON</td>
                <td><?= $r['zile_activitate'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($res_complex)): ?>
                <tr><td colspan="4">Niciun client nu îndeplinește condițiile stricte.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Raport Detaliat Abonamente</h2>
    <p><em>Ordonat după: Nume, Dată, Rest de plată descrescător</em></p>
    <table>
        <thead>
            <tr>
                <th>Nume</th>
                <th>Prenume</th>
                <th>Data Achiziției</th>
                <th>Preț</th>
                <th>Rest Plată</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($res_detaliat as $r): ?>
            <tr>
                <td><?= $r['nume'] ?></td>
                <td><?= $r['prenume'] ?></td>
                <td><?= $r['data_achizitie'] ?></td>
                <td><?= $r['pret'] ?></td>
                <td style="color: <?= $r['rest_plata'] > 0 ? 'red' : 'green' ?>">
                    <?= number_format($r['rest_plata'], 2) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Abonamente Achitate Integral</h2>
    <table>
        <thead>
            <tr>
                <th>Nume</th>
                <th>Serviciu</th>
                <th>Data</th>
                <th>Valoare</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($res_achitati as $r): ?>
            <tr>
                <td><?= $r['nume'] . " " . $r['prenume'] ?></td>
                <td><?= $r['serviciu'] ?></td>
                <td><?= $r['data_achizitie'] ?></td>
                <td><?= $r['pret'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>