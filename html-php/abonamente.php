<?php
require 'db.php';

// --- 1. PROCESARE FORMULAR (ADĂUGARE ABONAMENT) ---
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adauga_abonament'])) {
    try {
        // Apelam procedura stocata (Cerinta 4)
        $sql = "CALL adauga_abonament(:cnp, :serviciu, :pret)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cnp' => $_POST['cnp_client'],
            ':serviciu' => $_POST['serviciu'],
            ':pret' => $_POST['pret']
        ]);
        $message = "<div style='color:green; margin-bottom:15px;'>Abonament înregistrat! Soldul a fost actualizat automat.</div>";
    } catch (PDOException $e) {
        $message = "<div style='color:red; margin-bottom:15px;'>Eroare: " . $e->getMessage() . "</div>";
    }
}

// --- 2. PRELUARE DATE PENTRU DROPDOWN (Lista Clienți) ---
$stmt_clienti = $pdo->query("SELECT CNP, nume, prenume, disponibil FROM clienti ORDER BY nume");
$lista_clienti = $stmt_clienti->fetchAll();

// --- 3. PRELUARE ISTORIC ABONAMENTE (Cu Join pentru Nume) ---
// Aducem si numele clientului ca sa nu afisam doar CNP-uri
$sql_istoric = "SELECT a.*, c.nume, c.prenume 
                FROM abonamente a 
                JOIN clienti c ON a.CNP = c.CNP 
                ORDER BY a.data_achizitie DESC, a.id DESC";
$istoric = $pdo->query($sql_istoric)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestiune Abonamente</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="nav">
        <a href="clienti.php">Clienți</a>
        <a href="abonamente.php">Abonamente & Plăți</a>
        <a href="rapoarte.php">Rapoarte</a>
    </div>

    <div class="container">
        <h1>Emitere Abonament Nou</h1>
        <?= $message ?>

        <form method="POST" style="max-width: 500px;">
            <label>Selectează Client:</label>
            <select name="cnp_client" required>
                <option value="">-- Alege Client --</option>
                <?php foreach ($lista_clienti as $c): ?>
                    <option value="<?= $c['CNP'] ?>">
                        <?= $c['nume'] . " " . $c['prenume'] ?> (Sold: <?= $c['disponibil'] ?> RON)
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Serviciu:</label>
            <input type="text" name="serviciu" placeholder="Ex: Fitness, Sauna, Masaj" required>

            <label>Preț (RON):</label>
            <input type="number" step="0.01" name="pret" placeholder="0.00" required>

            <button type="submit" name="adauga_abonament">Înregistrează Abonament</button>
        </form>
    </div>

    <div class="container" style="margin-top: 20px;">
        <h2>Istoric Tranzacții</h2>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Client</th>
                    <th>Serviciu</th>
                    <th>Preț</th>
                    <th>Încasat</th>
                    <th>Rest Plată</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($istoric as $row): 
                    $rest = $row['pret'] - $row['suma_incasata'];
                    // Logică vizuală pentru State
                    $cssClass = ($rest <= 0) ? 'status-achitat' : 'status-restant';
                    $statusText = ($rest <= 0) ? 'ACHITAT' : 'RESTANT';
                ?>
                <tr class="<?= $cssClass ?>">
                    <td><?= $row['data_achizitie'] ?></td>
                    <td><?= $row['nume'] . " " . $row['prenume'] ?></td>
                    <td><?= $row['serviciu'] ?></td>
                    <td><?= number_format($row['pret'], 2) ?></td>
                    <td><?= number_format($row['suma_incasata'], 2) ?></td>
                    <td><strong><?= number_format($rest, 2) ?></strong></td>
                    <td><?= $statusText ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>