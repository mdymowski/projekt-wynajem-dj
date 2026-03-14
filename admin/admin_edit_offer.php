<?php
include '../config.php';

// Sprawdzenie, czy użytkownik jest zalogowany i ma uprawnienia administratora
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: ../login_register.php"); // Przekierowanie do strony logowania, jeśli nie jest zalogowany
    exit();
}

$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT id_uprawnienia FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jeśli użytkownik nie jest administratorem, przekierowanie
if (!$user || !in_array($user['id_uprawnienia'], [1])) {
    header("Location: ../user/user_dashboard.php");
    exit();
}

// Pobranie ID usługi do edycji
if (!isset($_GET['id_uslugi']) || !is_numeric($_GET['id_uslugi'])) {
    die("Nieprawidłowy identyfikator usługi.");
}

$id_uslugi = intval($_GET['id_uslugi']);

try {
    // Pobranie danych usługi
    $stmt = $pdo->prepare("SELECT nazwa, opis, cena, czas_trwania FROM oferta_uslug WHERE id_uslugi = ?");
    $stmt->execute([$id_uslugi]);
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$offer) {
        die("Nie znaleziono usługi do edycji.");
    }

    // Obsługa formularza edycji
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nazwa = trim($_POST['nazwa']);
        $opis = trim($_POST['opis']);
        $cena = floatval($_POST['cena']);
        $czas_trwania = intval($_POST['czas_trwania']);

        if (empty($nazwa) || empty($opis) || $cena <= 0 || $czas_trwania <= 0) {
            $error = "Wszystkie pola muszą być wypełnione, a cena i czas trwania muszą być większe od zera.";
        } else {
            $stmt = $pdo->prepare("UPDATE oferta_uslug SET nazwa = ?, opis = ?, cena = ?, czas_trwania = ? WHERE id_uslugi = ?");
            $stmt->execute([$nazwa, $opis, $cena, $czas_trwania, $id_uslugi]);

            $success = "Usługa została pomyślnie zaktualizowana.";
            $offer['nazwa'] = $nazwa;
            $offer['opis'] = $opis;
            $offer['cena'] = $cena;
            $offer['czas_trwania'] = $czas_trwania;
        }
    }
} catch (PDOException $e) {
    die("Błąd: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj usługę</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Edytuj usługę</h1>

        <?php if (isset($error)): ?>
            <div class="error"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"> <?= htmlspecialchars($success) ?> </div>
        <?php endif; ?>

        <form method="POST">
            <label for="nazwa">Nazwa usługi:</label>
            <input type="text" id="nazwa" name="nazwa" value="<?= htmlspecialchars($offer['nazwa']) ?>" required>

            <label for="opis">Opis usługi:</label>
            <textarea id="opis" name="opis" rows="5" required><?= htmlspecialchars($offer['opis']) ?></textarea>

            <label for="cena">Cena (PLN):</label>
            <input type="number" id="cena" name="cena" step="0.01" value="<?= htmlspecialchars($offer['cena']) ?>" required>

            <label for="czas_trwania">Czas trwania (minuty):</label>
            <input type="number" id="czas_trwania" name="czas_trwania" value="<?= htmlspecialchars($offer['czas_trwania']) ?>" required>

            <button type="submit" class="button">Zaktualizuj usługę</button>
        </form>

        <a href="admin_manage_offers.php" class="button">Powrót do zarządzania ofertami</a>
    </div>
</body>
</html>


