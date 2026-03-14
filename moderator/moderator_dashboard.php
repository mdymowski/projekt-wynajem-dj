<?php
session_start();
include '../config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['id_user'])) {
    header("Location: ../login_register.php"); // Przekierowanie do strony logowania, jeśli nie jest zalogowany
    exit();
}

$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT id_uprawnienia FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jeśli użytkownik nie jest moderatorem, przekierowanie
if (!$user || !in_array($user['id_uprawnienia'], [2])) {
    header("Location: ../user/user_dashboard.php");
    exit();
}

// Pobranie danych użytkownika
$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT login, id_uprawnienia FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jeśli nie znaleziono użytkownika w bazie
if (!$user) {
    header("Location: ../login_register.php");
    exit();
}

// Sprawdzamy, czy użytkownik jest moderatorem
$is_moderator = $user['id_uprawnienia'] == 2; // Sprawdzamy, czy ma uprawnienia moderatora (id_uprawnienia = 2)
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel moderatora</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Panel Moderatora</h1>
        <div class="user-info">
            <p><strong>Login:</strong> <?= htmlspecialchars($user['login']) ?></p>
            <p><strong>Uprawnienia:</strong> <?= htmlspecialchars($user['id_uprawnienia']) ?></p>
        </div>
        
        <div class="buttons">
            <!-- Wyświetl tylko przycisk do zarządzania opiniami, jeśli użytkownik jest moderatorem -->
            <?php if ($is_moderator): ?>
                <a href="moderator_manage_opinions.php" class="button">Zarządzaj opiniami użytkowników</a>
                <a href="moderator_manage_questions.php" class="button">Zarządzaj pytaniami użytkowników</a>
            <?php endif; ?>
            <br>
            <a href="../logout.php" class="button">Wyloguj się</a> <!-- Link do wylogowania -->
        </div>
    </div>
</body>
</html>


