<?php
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login_register.php'); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
    exit();
}

$id_user = $_SESSION['id_user'];

// Pobranie ID pytania do edycji
if (!isset($_GET['id_faq']) || !is_numeric($_GET['id_faq'])) {
    die("Nieprawidłowy identyfikator pytania.");
}

$id_faq = intval($_GET['id_faq']);

try {
    // Pobranie pytania użytkownika
    $stmt = $pdo->prepare("SELECT pytanie, odpowiedz FROM faq WHERE id_faq = ? AND id_user = ?");
    $stmt->execute([$id_faq, $id_user]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        die("Nie znaleziono pytania lub brak uprawnień do edycji.");
    }

    // Sprawdzenie, czy pytanie posiada odpowiedź
    if (!empty($question['odpowiedz'])) {
        die("Nie można edytować pytania, które posiada już odpowiedź.");
    }

    // Obsługa formularza edycji
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_question = trim($_POST['question']);

        if (empty($new_question)) {
            $error = "Pytanie nie może być puste!";
        } else {
            $stmt = $pdo->prepare("UPDATE faq SET pytanie = ? WHERE id_faq = ? AND id_user = ?");
            $stmt->execute([$new_question, $id_faq, $id_user]);

            $success = "Pytanie zostało pomyślnie zaktualizowane.";
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
    <title>Edytuj pytanie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edytuj pytanie</h1>

        <?php if (isset($error)): ?>
            <div class="error"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"> <?= htmlspecialchars($success) ?> </div>
        <?php endif; ?>

        <form method="POST">
            <label for="question">Treść pytania:</label>
            <textarea id="question" name="question" rows="5" required><?= htmlspecialchars($question['pytanie']) ?></textarea>

            <button type="submit" class="button">Zaktualizuj pytanie</button>
        </form>

        <a href="user_questions.php" class="button">Wróć do zarządzania pytaniami</a>
    </div>
</body>
</html>
