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

// Pobranie ID pytania do odpowiedzi
if (!isset($_GET['id_faq']) || !is_numeric($_GET['id_faq'])) {
    die("Nieprawidłowy identyfikator pytania.");
}

$id_faq = intval($_GET['id_faq']);

try {
    // Pobranie pytania
    $stmt = $pdo->prepare("SELECT pytanie, odpowiedz FROM faq WHERE id_faq = ?");
    $stmt->execute([$id_faq]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        die("Nie znaleziono pytania.");
    }

    // Sprawdzenie, czy pytanie nie ma już odpowiedzi
    if (!empty($question['odpowiedz'])) {
        die("To pytanie posiada już odpowiedź.");
    }

    // Obsługa formularza odpowiedzi
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $answer = trim($_POST['answer']);

        if (empty($answer)) {
            $error = "Odpowiedź nie może być pusta!";
        } else {
            $stmt = $pdo->prepare("UPDATE faq SET odpowiedz = ? WHERE id_faq = ?");
            $stmt->execute([$answer, $id_faq]);

            $success = "Odpowiedź została pomyślnie dodana.";
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
    <title>Odpowiedz na pytanie</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Odpowiedz na pytanie</h1>

        <?php if (isset($error)): ?>
            <div class="error"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"> <?= htmlspecialchars($success) ?> </div>
        <?php endif; ?>

        <div class="question">
            <p><strong>Pytanie:</strong> <?= nl2br(htmlspecialchars($question['pytanie'])) ?></p>
        </div>

        <form method="POST">
            <label for="answer">Treść odpowiedzi:</label>
            <textarea id="answer" name="answer" rows="5" required></textarea>

            <button type="submit" class="button">Dodaj odpowiedź</button>
        </form>

        <a href="admin_manage_questions.php" class="button">Powrót do zarządzania pytaniami</a>
    </div>
</body>
</html>


