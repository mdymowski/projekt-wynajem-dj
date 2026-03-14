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

// Pobranie ID promocji do edycji
if (!isset($_GET['id_promocji']) || !is_numeric($_GET['id_promocji'])) {
    die("Nieprawidłowy identyfikator promocji.");
}

$id_promocji = intval($_GET['id_promocji']);

try {
    // Pobranie danych promocji
    $stmt = $pdo->prepare("SELECT nazwa, opis, rabat, data_rozpoczecia, data_zakonczenia FROM promocje WHERE id_promocji = ?");
    $stmt->execute([$id_promocji]);
    $promotion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$promotion) {
        die("Nie znaleziono promocji do edycji.");
    }

    // Pobranie usług dla opcji wyboru
    $stmt_services = $pdo->query("SELECT id_uslugi, nazwa, id_promocji FROM oferta_uslug");
    $services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

    // Obsługa formularza edycji
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nazwa = trim($_POST['nazwa']);
        $opis = trim($_POST['opis']);
        $rabat = floatval($_POST['rabat']);
        $data_rozpoczecia = $_POST['data_rozpoczecia'];
        $data_zakonczenia = $_POST['data_zakonczenia'];
        $id_uslugi = $_POST['id_uslugi'] === 'all' ? null : intval($_POST['id_uslugi']);

        if (empty($nazwa) || empty($opis) || $rabat <= 0 || empty($data_rozpoczecia) || empty($data_zakonczenia)) {
            $error = "Wszystkie pola muszą być wypełnione, a rabat musi być większy od zera.";
        } else {
            // Aktualizacja promocji
            $stmt = $pdo->prepare("UPDATE promocje SET nazwa = ?, opis = ?, rabat = ?, data_rozpoczecia = ?, data_zakonczenia = ? WHERE id_promocji = ?");
            $stmt->execute([$nazwa, $opis, $rabat, $data_rozpoczecia, $data_zakonczenia, $id_promocji]);

            // Jeśli promocja dotyczy konkretnej usługi, aktualizacja tabeli oferta_uslug
            if ($id_uslugi !== null) {
                $stmt = $pdo->prepare("UPDATE oferta_uslug SET id_promocji = NULL WHERE id_promocji = ?");
                $stmt->execute([$id_promocji]);

                $stmt = $pdo->prepare("UPDATE oferta_uslug SET id_promocji = ? WHERE id_uslugi = ?");
                $stmt->execute([$id_promocji, $id_uslugi]);
            } else {
                $stmt = $pdo->prepare("UPDATE oferta_uslug SET id_promocji = NULL WHERE id_promocji = ?");
                $stmt->execute([$id_promocji]);
            }

            $success = "Promocja została pomyślnie zaktualizowana.";
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
    <title>Edytuj promocję</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Edytuj promocję</h1>

        <?php if (isset($error)): ?>
            <div class="error"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"> <?= htmlspecialchars($success) ?> </div>
        <?php endif; ?>

        <form method="POST">
            <label for="nazwa">Nazwa promocji:</label>
            <input type="text" id="nazwa" name="nazwa" value="<?= htmlspecialchars($promotion['nazwa']) ?>" required>

            <label for="opis">Opis promocji:</label>
            <textarea id="opis" name="opis" rows="5" required><?= htmlspecialchars($promotion['opis']) ?></textarea>

            <label for="rabat">Rabat (%):</label>
            <input type="number" id="rabat" name="rabat" step="0.01" value="<?= htmlspecialchars($promotion['rabat']) ?>" required>

            <label for="data_rozpoczecia">Data rozpoczęcia:</label>
            <input type="date" id="data_rozpoczecia" name="data_rozpoczecia" value="<?= htmlspecialchars($promotion['data_rozpoczecia']) ?>" required>

            <label for="data_zakonczenia">Data zakończenia:</label>
            <input type="date" id="data_zakonczenia" name="data_zakonczenia" value="<?= htmlspecialchars($promotion['data_zakonczenia']) ?>" required>

            <label for="id_uslugi">Dotyczy usługi:</label>
            <select id="id_uslugi" name="id_uslugi">
                <option value="all">Wszystkie usługi</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id_uslugi'] ?>" <?= $service['id_promocji'] == $id_promocji ? 'selected' : '' ?>><?= htmlspecialchars($service['nazwa']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="button">Zaktualizuj promocję</button>
        </form>

        <a href="admin_promo.php" class="button">Powrót do zarządzania promocjami</a>
    </div>
</body>
</html>


