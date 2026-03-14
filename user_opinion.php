<?php
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login_register.php'); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
    exit();
}

$id_user = $_SESSION['id_user'];

// Pobranie opinii użytkownika z bazy
$stmt = $pdo->prepare("SELECT * FROM opinie_klientow WHERE id_user = ?");
$stmt->execute([$id_user]);
$opinie = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twoje opinie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Twoje opinie</h1>

        <?php if (empty($opinie)): ?>
            <p>Nie masz żadnych opinii.</p>
        <?php else: ?>
            <?php foreach ($opinie as $opinia): ?>
                <div class="opinia">
                    <p><strong>Ocena:</strong> <?= htmlspecialchars($opinia['ocena']) ?> / 5</p>
                    <p><strong>Opinia:</strong> <?= nl2br(htmlspecialchars($opinia['tresc'])) ?></p>
                    <p><small><em>Dodano: <?= $opinia['data_opinii'] ?></em></small></p>

                    <!-- Formularz edytowania opinii -->
                    <a href="user_edit_opinion.php?id_opinii=<?= $opinia['id_opinii'] ?>" class="button">Edytuj opinię</a>

                    <!-- Formularz usuwania opinii -->
                    <a href="user_delete_opinion.php?id_opinii=<?= $opinia['id_opinii'] ?>" class="button" onclick="return confirm('Czy na pewno chcesz usunąć tę opinię?');">Usuń opinię</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <br>
        <a href="user_add_opinion.php" class="button">Dodaj opinię</a>
        <br>
        <a href="user_dashboard.php" class="button">Powrót do panelu użytkownika</a>
    </div>
</body>
</html>
