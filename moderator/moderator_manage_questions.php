<?php
include '../config.php';

// Sprawdzenie, czy użytkownik jest zalogowany i ma uprawnienia moderatora
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: ../login_register.php"); // Przekierowanie do strony logowania, jeśli nie jest zalogowany
    exit();
}

$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT id_uprawnienia FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jeśli użytkownik nie jest moderatorem - przekierowanie
if (!$user || !in_array($user['id_uprawnienia'], [2])) {
    header("Location: ../user/user_dashboard.php");
    exit();
}

// Pobranie wszystkich pytań z bazy
$stmt = $pdo->prepare("SELECT f.id_faq, f.pytanie, f.odpowiedz, f.data_pytania, u.imie, u.nazwisko FROM faq f JOIN uzytkownicy u ON f.id_user = u.id_user");
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzaj pytaniami</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Zarządzaj pytaniami użytkowników</h1>
        
        <?php if (empty($questions)): ?>
            <p>Brak pytań do moderowania.</p>
        <?php else: ?>
            <?php foreach ($questions as $question): ?>
                <div class="question">
                    <p><strong>Pytanie:</strong> <?= nl2br(htmlspecialchars($question['pytanie'])) ?></p>
                    <?php if (!empty($question['odpowiedz'])): ?>
                        <p><strong>Odpowiedź:</strong> <?= nl2br(htmlspecialchars($question['odpowiedz'])) ?></p>
                    <?php else: ?>
                        <p><strong>Odpowiedź:</strong> <em>Brak odpowiedzi</em></p>
                    <?php endif; ?>
                    <p><strong>Autor:</strong> <?= htmlspecialchars($question['imie']) ?> <?= htmlspecialchars($question['nazwisko']) ?></p>
                    <p><small><em>Dodano: <?= htmlspecialchars($question['data_pytania']) ?></em></small></p>

                    <!-- Link do odpowiadania na pytanie -->
                    <?php if (empty($question['odpowiedz'])): ?>
                        <a href="moderator_answer_question.php?id_faq=<?= $question['id_faq'] ?>" class="button">Odpowiedz na pytanie</a>
                    <?php endif; ?>

                    <!-- Link do edycji pytania -->
                    <a href="moderator_edit_question.php?id_faq=<?= $question['id_faq'] ?>" class="button">Edytuj pytanie</a>

                    <!-- Link do usunięcia pytania -->
                    <a href="moderator_delete_question.php?id_faq=<?= $question['id_faq'] ?>" class="button" onclick="return confirm('Czy na pewno chcesz usunąć to pytanie?');">Usuń pytanie</a>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="moderator_dashboard.php" class="button">Powrót do panelu moderatora</a>
    </div>
</body>
</html>


