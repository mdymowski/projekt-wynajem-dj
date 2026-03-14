<?php
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login_register.php'); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
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

$id_user = $_SESSION['id_user'];

// Pobranie uprawnień użytkownika
$stmt = $pdo->prepare("SELECT id_uprawnienia FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$id_user]);
$user = $stmt->fetch();

// Sprawdzanie, czy użytkownik ma uprawnienia moderatora (id_uprawnienia = 2) lub administratora
if (!$user || !in_array($user['id_uprawnienia'], [2])) {
    echo "Nie masz uprawnień do usunięcia tej opinii.";
    exit();
}

// Pobranie ID opinii do usunięcia
if (!isset($_GET['id_opinii'])) {
    echo "Brak ID opinii.";
    exit();
}

$id_opinii = $_GET['id_opinii'];

// Usuwanie opinii
try {
    // Umożliwienie usunięcia opinii przez moderatora bez względu na użytkownika
    $stmt = $pdo->prepare("DELETE FROM opinie_klientow WHERE id_opinii = ?");
    $stmt->execute([$id_opinii]);

    // Przekierowanie po usunięciu opinii
    header("Location: moderator_manage_opinions.php");
    exit();

} catch (Exception $e) {
    echo "Błąd podczas usuwania opinii: " . $e->getMessage();
    exit();
}
?>
