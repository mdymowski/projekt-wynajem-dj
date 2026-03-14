<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login_register.php"); // Przekierowanie do strony logowania, jeśli nie jest zalogowany
    exit();
}
// sprawdzenie czy admin
$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT id_uprawnienia FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jeśli użytkownik nie jest administratorem, przekierowanie
if (!$user || !in_array($user['id_uprawnienia'], [1])) {
    header("Location: ../user/user_dashboard.php");
    exit();
}

// Pobranie danych użytkownika
$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT id_uprawnienia, login FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
    <h1>Panel Administracyjny</h1>
        <div class="user-info">
            <p><strong>Login:</strong> <?= htmlspecialchars($user['login']) ?></p>
            <p><strong>Uprawnienia:</strong> <?= htmlspecialchars($user['id_uprawnienia']) ?></p>
        </div>
        
        <div class="buttons">
            <a href="admin_orders.php" class="button">Zarządzanie rezerwacjami</a>
            <a href="admin_edit_user.php" class="button">Edytuj dane użytkowników</a>
            <a href="admin_manage_opinions.php" class="button">Zarządzaj opiniami użytkowników</a>
            <a href="admin_manage_questions.php" class="button">Zarządzaj pytaniami użytkowników</a>
            <a href="admin_manage_offers.php" class="button">Zarządzaj ofertą usług</a>
            <a href="admin_promo.php" class="button">Zarządzaj promocjami</a>
            <a href="admin_roles.php" class="button">Zarządzaj rolami</a>
            <br>
            <a href="../logout.php" class="button">Wyloguj się</a> <!-- Link do wylogowania -->
        </div>
    </div>
</body>
</html>


