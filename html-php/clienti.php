<?php
require 'db.php'; // Includem conexiunea

// --- LOGICA DE ADĂUGARE CLIENT (INSERT) ---
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adauga_client'])) {
    try {
        $sql = "INSERT INTO clienti (CNP, nume, prenume, adresa, telefon, disponibil) 
                VALUES (:cnp, :nume, :prenume, :adresa, :telefon, :disponibil)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cnp' => $_POST['cnp'],
            ':nume' => $_POST['nume'],
            ':prenume' => $_POST['prenume'],
            ':adresa' => $_POST['adresa'],
            ':telefon' => $_POST['telefon'],
            ':disponibil' => $_POST['disponibil']
        ]);
        $message = "<div style='color:green'>Client adăugat cu succes!</div>";
    } catch (PDOException $e) {
        // Aici prindem erorile, inclusiv duplicat CNP sau sold negativ
        $message = "<div style='color:red'>Eroare: " . $e->getMessage() . "</div>";
    }
}

// --- LOGICA DE AFIȘARE CLIENȚI (SELECT) ---
$stmt = $pdo->query("SELECT * FROM clienti");
$clienti = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestiune Clienți Fitness</title>
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
            <a class="nav-link active" href="clienti.php">Clienți</a>
            <span class="visually-hidden">(current)</span>
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

    <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Gestiune Clienți</h1>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-5">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0"><i class="bi bi-person-plus"></i> Adaugă Client Nou</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">CNP</label>
                        <input type="text" name="cnp" class="form-control" placeholder="13 cifre" required maxlength="13">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nume</label>
                        <input type="text" name="nume" class="form-control" placeholder="Ex: Popescu" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Prenume</label>
                        <input type="text" name="prenume" class="form-control" placeholder="Ex: Andrei" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Adresa</label>
                        <input type="text" name="adresa" class="form-control" placeholder="Strada, Număr, Oraș">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Telefon</label>
                        <input type="text" name="telefon" class="form-control" placeholder="07xx xxx xxx">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sold Disponibil (RON)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="disponibil" class="form-control" placeholder="0.00" required>
                            <span class="input-group-text">RON</span>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" name="adauga_client" class="btn btn-success w-100">
                            <i class="bi bi-save"></i> Adaugă Client
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

</body>
</html>