<?php
include '../config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: ../login_register.php'); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
    exit();
}

$id_user = $_SESSION['id_user'];

// Obsługa formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobieranie danych z formularza
    $opinia = trim($_POST['opinia']);
    $ocena = $_POST['ocena'];
    $data = date('Y-m-d');

    // Walidacja opinii
    if (empty($opinia)) {
        $error = "Opinia nie może być pusta!";
    } elseif ($ocena < 1 || $ocena > 5) {
        $error = "Ocena musi być w zakresie od 1 do 5.";
    } else {
        try {
            // Dodanie opinii do bazy danych
            $stmt = $pdo->prepare("INSERT INTO opinie_klientow (id_user, tresc, data_opinii, ocena) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_user, $opinia, $data, $ocena]);

            // Przekierowanie po dodaniu opinii
            header("Location: user_add_opinion_confirm.php");
            exit();
        } catch (Exception $e) {
            $error = "Wystąpił błąd podczas dodawania opinii: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wystaw opinię</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Wystaw opinię</h1>

        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="opinia">Opinia:</label>
            <textarea id="opinia" name="opinia" rows="4" required></textarea>

            <label for="ocena">Ocena (1-5):</label>
            <input type="number" id="ocena" name="ocena" min="1" max="5" required>

            <button type="submit" class="button">Wystaw opinię</button>
        </form>

        <a href="user_dashboard.php" class="button">Powrót do panelu użytkownika</a>
    </div>
</body>
</html>


