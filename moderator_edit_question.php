<?php
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany i ma uprawnienia moderatora
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: login_register.php"); // Przekierowanie do strony logowania, jeśli nie jest zalogowany
    exit();
}

$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT id_uprawnienia FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jeśli użytkownik nie jest moderatorem, przekierowanie
if (!$user || !in_array($user['id_uprawnienia'], [2])) {
    header("Location: user_dashboard.php");
    exit();
}

// Pobranie ID pytania do edycji
if (!isset($_GET['id_faq']) || !is_numeric($_GET['id_faq'])) {
    die("Nieprawidłowy identyfikator pytania.");
}

$id_faq = intval($_GET['id_faq']);

try {
    // Pobranie pytania i odpowiedzi
    $stmt = $pdo->prepare("SELECT pytanie, odpowiedz FROM faq WHERE id_faq = ?");
    $stmt->execute([$id_faq]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        die("Nie znaleziono pytania.");
    }

    // Obsługa formularza edycji
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_question = trim($_POST['question']);
        $new_answer = isset($_POST['answer']) ? trim($_POST['answer']) : null;

        if (empty($new_question)) {
            $error = "Treść pytania nie może być pusta!";
        } else {
            // Aktualizacja pytania i odpowiedzi
            $stmt = $pdo->prepare("UPDATE faq SET pytanie = ?, odpowiedz = ? WHERE id_faq = ?");
            $stmt->execute([$new_question, $new_answer, $id_faq]);

            $success = "Pytanie i odpowiedź zostały pomyślnie zaktualizowane.";
            $question['pytanie'] = $new_question;
            $question['odpowiedz'] = $new_answer;
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

            <label for="answer">Treść odpowiedzi (jeśli dotyczy):</label>
            <textarea id="answer" name="answer" rows="5"><?= htmlspecialchars($question['odpowiedz']) ?></textarea>

            <button type="submit" class="button">Zaktualizuj pytanie</button>
        </form>

        <a href="moderator_manage_questions.php" class="button">Powrót do zarządzania pytaniami</a>
    </div>
</body>
</html>
