<?php
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany i ma uprawnienia moderatora lub administratora
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

// Pobranie wszystkich opinii z bazy
$stmt = $pdo->prepare("SELECT o.id_opinii, o.tresc, o.ocena, o.data_opinii, u.imie, u.nazwisko FROM opinie_klientow o JOIN uzytkownicy u ON o.id_user = u.id_user");
$stmt->execute();
$opinie = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzaj opiniami</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Zarządzaj opiniami użytkowników</h1>
        
        <?php if (empty($opinie)): ?>
            <p>Brak opinii do moderowania.</p>
        <?php else: ?>
            <?php foreach ($opinie as $opinia): ?>
                <div class="opinia">
                    <p><strong>Ocena:</strong> <?= htmlspecialchars($opinia['ocena']) ?> / 5</p>
                    <p><strong>Opinia:</strong> <?= nl2br(htmlspecialchars($opinia['tresc'])) ?></p>
                    <p><strong>Autor:</strong> <?= htmlspecialchars($opinia['imie']) ?> <?= htmlspecialchars($opinia['nazwisko']) ?></p>
                    <p><small><em>Dodano: <?= htmlspecialchars($opinia['data_opinii']) ?></em></small></p>

                    <!-- Link do edycji opinii -->
                    <a href="moderator_edit_opinion.php?id_opinii=<?= $opinia['id_opinii'] ?>" class="button">Edytuj opinię</a>

                    <!-- Link do usunięcia opinii -->
                    <a href="moderator_delete_opinion.php?id_opinii=<?= $opinia['id_opinii'] ?>" class="button" onclick="return confirm('Czy na pewno chcesz usunąć tę opinię?');">Usuń opinię</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="moderator_dashboard.php" class="button">Powrót do panelu moderatora</a>
    </div>
</body>
</html>
