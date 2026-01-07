<?php
require 'db.php';

function validateCNP($cnp) {

    if (strlen($cnp) !== 13 || !ctype_digit($cnp)) {
        return false;
    }

    $controlKey = [2, 7, 9, 1, 4, 6, 3, 5, 8, 2, 7, 9];
    $sum = 0;


    for ($i = 0; $i < 12; $i++) {
        $sum += $cnp[$i] * $controlKey[$i];
    }

    $remainder = $sum % 11;

    $calculatedControlDigit = ($remainder == 10) ? 1 : $remainder;


    if ($calculatedControlDigit != $cnp[12]) {
        return false;
    }

    return true;
}

$message = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adauga_client'])) {
    

    $cnp = trim($_POST['cnp']);
    

    if (!validateCNP($cnp)) {
        $message = "<div class='alert alert-danger'>
                        <i class='bi bi-exclamation-triangle-fill'></i> 
                        <strong>Eroare:</strong> CNP-ul introdus este invalid (Cifra de control incorectă).
                    </div>";
    } else {
        // Dacă e valid, încercăm inserarea
        try {
            $sql = "INSERT INTO clienti (CNP, nume, prenume, adresa, telefon, disponibil) 
                    VALUES (:cnp, :nume, :prenume, :adresa, :telefon, :disponibil)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':cnp' => $cnp,
                ':nume' => $_POST['nume'],
                ':prenume' => $_POST['prenume'],
                ':adresa' => $_POST['adresa'],
                ':telefon' => $_POST['telefon'],
                ':disponibil' => $_POST['disponibil']
            ]);
            
            $message = "<div class='alert alert-success'>
                            <i class='bi bi-check-circle-fill'></i> Client adăugat cu succes!
                        </div>";
            

            $_POST = []; 
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                 $message = "<div class='alert alert-warning'>Acest CNP există deja în baza de date!</div>";
            } else {
                 $message = "<div class='alert alert-danger'>Eroare SQL: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// --- 3. AFIȘARE CLIENȚI ---
$stmt = $pdo->query("SELECT * FROM clienti ORDER BY nume ASC");
$clienti = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestiune Clienți Fitness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Evidență Abonamente</a>
            <div class="collapse navbar-collapse" id="navbarColor01">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="clienti.php">Clienți</a></li>
                    <li class="nav-item"><a class="nav-link" href="abonamente.php">Abonamente</a></li>
                    <li class="nav-item"><a class="nav-link" href="rapoarte.php">Rapoarte</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">Gestiune Clienți</h1>
        </div>

        <?= $message ?>

        <div class="card shadow-sm mb-5">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="bi bi-person-plus"></i> Adaugă Client Nou</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">CNP <span class="text-danger">*</span></label>
                            <input type="text" name="cnp" class="form-control" placeholder="13 cifre" maxlength="13" required
                                   value="<?= htmlspecialchars($_POST['cnp'] ?? '') ?>">
                            <div class="form-text">Va fi validat prin algoritm standard.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nume <span class="text-danger">*</span></label>
                            <input type="text" name="nume" class="form-control" required
                                   value="<?= htmlspecialchars($_POST['nume'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prenume <span class="text-danger">*</span></label>
                            <input type="text" name="prenume" class="form-control" required
                                   value="<?= htmlspecialchars($_POST['prenume'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Adresa</label>
                            <input type="text" name="adresa" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['adresa'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Telefon</label>
                            <input type="text" name="telefon" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['telefon'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sold Disponibil (RON) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="disponibil" class="form-control" required
                                       value="<?= htmlspecialchars($_POST['disponibil'] ?? '') ?>">
                                <span class="input-group-text">RON</span>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" name="adauga_client" class="btn btn-success w-100">
                                <i class="bi bi-save"></i> Salvează Client
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Lista Clienți</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>CNP</th>
                                <th>Nume</th>
                                <th>Prenume</th>
                                <th>Adresă</th>
                                <th>Telefon</th>
                                <th class="text-end">Sold Disponibil</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clienti as $client): ?>
                            <tr>
                                <td class="font-monospace"><?= htmlspecialchars($client['CNP']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($client['nume']) ?></td>
                                <td><?= htmlspecialchars($client['prenume']) ?></td>
                                <td class="small text-muted"><?= htmlspecialchars($client['adresa']) ?></td>
                                <td><?= htmlspecialchars($client['telefon']) ?></td>
                                
                                <td class="text-end <?= $client['disponibil'] > 0 ? 'text-success fw-bold' : 'text-danger fw-bold' ?>">
                                    <?= number_format($client['disponibil'], 2) ?> RON
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>