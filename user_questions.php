<?php
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login_register.php'); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
    exit();
}

$id_user = $_SESSION['id_user'];

// Pobranie pytań użytkownika z bazy
$stmt = $pdo->prepare("SELECT * FROM faq WHERE id_user = ?");
$stmt->execute([$id_user]);
$pytania = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twoje pytania</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Twoje pytania</h1>

        <?php if (empty($pytania)): ?>
            <p>Nie masz żadnych pytań.</p>
        <?php else: ?>
            <?php foreach ($pytania as $pytanie): ?>
                <div class="question">
                    <p><strong>Pytanie:</strong> <?= nl2br(htmlspecialchars($pytanie['pytanie'])) ?></p>
                    <?php if (!empty($pytanie['odpowiedz'])): ?>
                        <p><strong>Odpowiedź:</strong> <?= nl2br(htmlspecialchars($pytanie['odpowiedz'])) ?></p>
                    <?php else: ?>
                        <p><strong>Odpowiedź:</strong> <em>Brak odpowiedzi</em></p>
                    <?php endif; ?>
                    <p><small><em>Dodano: <?= htmlspecialchars($pytanie['data_pytania']) ?></em></small></p>

                    <!-- Formularz edytowania pytania -->
                    <a href="user_edit_question.php?id_faq=<?= $pytanie['id_faq'] ?>" class="button">Edytuj pytanie</a>

                    <!-- Formularz usuwania pytania -->
                    <a href="user_delete_question.php?id_faq=<?= $pytanie['id_faq'] ?>" class="button" onclick="return confirm('Czy na pewno chcesz usunąć to pytanie?');">Usuń pytanie</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <br>
        <a href="user_add_question.php" class="button">Dodaj pytanie</a>
        <br>
        <a href="user_dashboard.php" class="button">Powrót do panelu użytkownika</a>
    </div>
</body>
</html>
