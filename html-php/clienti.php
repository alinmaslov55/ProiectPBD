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
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .nav { margin-bottom: 20px; background: #333; padding: 10px; }
        .nav a { color: white; text-decoration: none; margin-right: 15px; }
        .form-container { background: #f9f9f9; padding: 15px; border: 1px solid #ddd; }
        input { margin: 5px 0; padding: 5px; width: 200px; display:block; }
    </style>
</head>
<body>

    <div class="nav">
        <a href="clienti.php">Clienți</a>
        <a href="abonamente.php">Abonamente & Plăți</a>
        <a href="rapoarte.php">Rapoarte</a>
    </div>

    <h1>Gestiune Clienți</h1>

    <?= $message ?>

    <div class="form-container">
        <h3>Adaugă Client Nou</h3>
        <form method="POST">
            <input type="text" name="cnp" placeholder="CNP (13 cifre)" required maxlength="13">
            <input type="text" name="nume" placeholder="Nume" required>
            <input type="text" name="prenume" placeholder="Prenume" required>
            <input type="text" name="adresa" placeholder="Adresa">
            <input type="text" name="telefon" placeholder="Telefon (9 cifre)">
            <input type="number" step="0.01" name="disponibil" placeholder="Sold Disponibil (RON)" required>
            <button type="submit" name="adauga_client">Adaugă Client</button>
        </form>
    </div>

    <h3>Lista Clienți</h3>
    <table>
        <thead>
            <tr>
                <th>CNP</th>
                <th>Nume</th>
                <th>Prenume</th>
                <th>Adresă</th>
                <th>Telefon</th>
                <th>Sold Disponibil</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clienti as $client): ?>
            <tr>
                <td><?= htmlspecialchars($client['CNP']) ?></td>
                <td><?= htmlspecialchars($client['nume']) ?></td>
                <td><?= htmlspecialchars($client['prenume']) ?></td>
                <td><?= htmlspecialchars($client['adresa']) ?></td>
                <td><?= htmlspecialchars($client['telefon']) ?></td>
                <td style="font-weight:bold; color: <?= $client['disponibil'] > 0 ? 'green' : 'red' ?>">
                    <?= number_format($client['disponibil'], 2) ?> RON
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>