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
            <a class="nav-link active" href="abonamente.php">Abonamente</a>
            <span class="visually-hidden">(current)</span>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="rapoarte.php">Rapoarte</a>
            </li>
        </ul>
        </div>
    </div>
    </nav>

<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <div class="me-auto">
            <h1 class="h2"><i class="bi bi-receipt-cutoff"></i> Emitere Abonament</h1>
            <p class="text-muted mb-0">Înregistrează un serviciu nou pentru un client existent.</p>
        </div>
    </div>

    <?= $message ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h5 class="card-title mb-3">Detalii Tranzacție</h5>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Selectează Client</label>
                            <select name="cnp_client" class="form-select" required>
                                <option value="">-- Alege Client --</option>
                                <?php foreach ($lista_clienti as $c): ?>
                                    <option value="<?= $c['CNP'] ?>">
                                        <?= htmlspecialchars($c['nume'] . " " . $c['prenume']) ?> 
                                        (Sold: <?= $c['disponibil'] ?> RON)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Asigură-te că soldul acoperă serviciul.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Serviciu</label>
                            <input type="text" name="serviciu" class="form-control" placeholder="Ex: Fitness, Sauna" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preț (RON)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="pret" class="form-control" placeholder="0.00" required>
                                <span class="input-group-text">RON</span>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" name="adauga_abonament" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Înregistrează Abonament
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Istoric Tranzacții Recente</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Client</th>
                                <th>Serviciu</th>
                                <th class="text-end">Preț</th>
                                <th class="text-end">Încasat</th>
                                <th class="text-end">Rest Plată</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($istoric as $row): 
                                $rest = $row['pret'] - $row['suma_incasata'];
                                // Logic for Bootstrap Classes
                                $isPaid = ($rest <= 0);
                                $badgeClass = $isPaid ? 'bg-success' : 'bg-danger';
                                $textClass = $isPaid ? 'text-muted' : 'text-danger fw-bold';
                                $statusIcon = $isPaid ? '<i class="bi bi-check-lg"></i>' : '<i class="bi bi-exclamation-circle"></i>';
                            ?>
                            <tr>
                                <td class="small text-muted"><?= $row['data_achizitie'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nume'] . " " . $row['prenume']) ?></td>
                                <td><?= htmlspecialchars($row['serviciu']) ?></td>
                                <td class="text-end"><?= number_format($row['pret'], 2) ?></td>
                                <td class="text-end"><?= number_format($row['suma_incasata'], 2) ?></td>
                                
                                <td class="text-end <?= $textClass ?>">
                                    <?= number_format($rest, 2) ?>
                                </td>

                                <td class="text-center">
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= $statusIcon ?> <?= $isPaid ? 'ACHITAT' : 'RESTANT' ?>
                                    </span>
                                </td>
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