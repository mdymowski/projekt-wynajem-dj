<?php
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany i ma uprawnienia administratora
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: login_register.php"); // Przekierowanie do strony logowania, jeśli nie jest zalogowany
    exit();
}

$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT id_uprawnienia FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jeśli użytkownik nie jest administratorem, przekierowanie
if (!$user || !in_array($user['id_uprawnienia'], [1])) {
    header("Location: user_dashboard.php");
    exit();
}

// Obsługa formularza dodawania usługi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nazwa = trim($_POST['nazwa']);
    $opis = trim($_POST['opis']);
    $cena = floatval($_POST['cena']);
    $czas_trwania = intval($_POST['czas_trwania']);

    if (empty($nazwa) || empty($opis) || $cena <= 0 || $czas_trwania <= 0) {
        $error = "Wszystkie pola muszą być wypełnione, a cena i czas trwania muszą być większe od zera.";
    } else {
        try {
            // Dodanie nowej usługi do bazy danych
            $stmt = $pdo->prepare("INSERT INTO oferta_uslug (nazwa, opis, cena, czas_trwania) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nazwa, $opis, $cena, $czas_trwania]);

            $success = "Nowa usługa została pomyślnie dodana.";
        } catch (PDOException $e) {
            $error = "Błąd podczas dodawania usługi: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj usługę</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Dodaj nową usługę</h1>

        <?php if (isset($error)): ?>
            <div class="error"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"> <?= htmlspecialchars($success) ?> </div>
        <?php endif; ?>

        <form method="POST">
            <label for="nazwa">Nazwa usługi:</label>
            <input type="text" id="nazwa" name="nazwa" required>

            <label for="opis">Opis usługi:</label>
            <textarea id="opis" name="opis" rows="5" required></textarea>

            <label for="cena">Cena (PLN):</label>
            <input type="number" id="cena" name="cena" step="0.01" required>

            <label for="czas_trwania">Czas trwania (minuty):</label>
            <input type="number" id="czas_trwania" name="czas_trwania" required>

            <button type="submit" class="button">Dodaj usługę</button>
        </form>

        <a href="admin_manage_offers.php" class="button">Powrót do zarządzania ofertą</a>
    </div>
</body>
</html>
