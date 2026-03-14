<?php
include '../config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: ../login_register.php'); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
    exit();
}

$id_user = $_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobranie danych z formularza
    $question = trim($_POST['question']);

    // Walidacja
    if (empty($question)) {
        $error = "Pytanie nie może być puste!";
    } else {
        try {
            // Dodanie pytania do tabeli `faq`
            $stmt = $pdo->prepare("INSERT INTO faq (id_user, pytanie, data_pytania) VALUES (?, ?, NOW())");
            $stmt->execute([$id_user, $question]);

            $success = "Pytanie zostało pomyślnie dodane.";
        } catch (PDOException $e) {
            $error = "Wystąpił błąd podczas dodawania pytania: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj pytanie</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Dodaj pytanie</h1>

        <?php if (isset($error)): ?>
            <div class="error"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"> <?= htmlspecialchars($success) ?> </div>
        <?php endif; ?>

        <form method="POST">
            <label for="question">Treść pytania:</label>
            <textarea id="question" name="question" rows="5" required></textarea>

            <button type="submit" class="button">Dodaj pytanie</button>
        </form>

        <a href="user_questions.php" class="button">Wróć do zarządzania pytaniami</a>
    </div>
</body>
</html>


