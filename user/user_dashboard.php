<?php
session_start();
include '../config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['id_user'])) {
    header("Location: ../login_register.php"); // Przekierowanie do strony logowania, jeśli nie jest zalogowany
    exit();
}

// Pobranie danych użytkownika
$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT id_uprawnienia, login, imie, nazwisko, email FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jeśli nie znaleziono użytkownika w bazie
if (!$user) {
    header("Location: ../login_register.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel użytkownika</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Witaj, <?= htmlspecialchars($user['imie']) ?>!</h1>
        <div class="user-info">
            <p><strong>Login:</strong> <?= htmlspecialchars($user['login']) ?></p>
            <p><strong>Imię:</strong> <?= htmlspecialchars($user['imie']) ?></p>
            <p><strong>Nazwisko:</strong> <?= htmlspecialchars($user['nazwisko']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Uprawnienia:</strong> <?= htmlspecialchars($user['id_uprawnienia']) ?></p>
        </div>
        
        <div class="buttons">
            <a href="user_orders.php" class="button">Moje rezerwacje</a>
            <a href="make_reservation.php" class="button">Zarezerwuj usługę</a> <!-- Link do rezerwacji -->
            <a href="edit_profile.php" class="button">Edytuj profil</a>
            <a href="user_opinion.php" class="button">Zarządzaj swoimi opiniami</a>
            <a href="user_questions.php" class="button">Zarządzaj swoimi pytaniami</a>
            <br>
            <a href="../logout.php" class="button">Wyloguj się</a> <!-- Link do wylogowania -->
        </div>
    </div>
</body>
</html>


