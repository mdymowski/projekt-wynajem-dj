<?php
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login_register.php'); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
    exit();
}

$id_user = $_SESSION['id_user'];

// Pobranie ID opinii do edytowania
if (!isset($_GET['id_opinii'])) {
    echo "Brak ID opinii.";
    exit();
}

$id_opinii = $_GET['id_opinii'];

// Pobranie danych opinii z bazy
$stmt = $pdo->prepare("SELECT * FROM opinie_klientow WHERE id_opinii = ? AND id_user = ?");
$stmt->execute([$id_opinii, $id_user]);
$opinia = $stmt->fetch();

if (!$opinia) {
    echo "Nie znaleziono opinii do edytowania.";
    exit();
}

// Obsługa formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opinia_text = trim($_POST['opinia']);
    $ocena = $_POST['ocena'];

    // Walidacja
    if (empty($opinia_text)) {
        $error = "Opinia nie może być pusta!";
    } elseif ($ocena < 1 || $ocena > 5) {
        $error = "Ocena musi być w zakresie od 1 do 5.";
    } else {
        try {
            // Aktualizacja opinii w bazie
            $stmt = $pdo->prepare("UPDATE opinie_klientow SET tresc = ?, ocena = ? WHERE id_opinii = ?");
            $stmt->execute([$opinia_text, $ocena, $id_opinii]);

            // Przekierowanie po zapisaniu zmian
            header("Location: user_opinion.php");
            exit();
        } catch (Exception $e) {
            $error = "Błąd podczas aktualizowania opinii: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj opinię</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edytuj opinię</h1>

        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="opinia">Opinia:</label>
            <textarea id="opinia" name="opinia" rows="4" required><?= htmlspecialchars($opinia['tresc']) ?></textarea>

            <label for="ocena">Ocena (1-5):</label>
            <input type="number" id="ocena" name="ocena" min="1" max="5" value="<?= htmlspecialchars($opinia['ocena']) ?>" required>

            <button type="submit" class="button">Zaktualizuj opinię</button>
        </form>
        <a href="user_opinion.php" class="button">Powrót do zarządzania opiniami</a>
    </div>
</body>
</html>
