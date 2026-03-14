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

// Pobranie ID usługi do usunięcia
if (!isset($_GET['id_uslugi']) || !is_numeric($_GET['id_uslugi'])) {
    die("Nieprawidłowy identyfikator usługi.");
}

$id_uslugi = intval($_GET['id_uslugi']);

try {
    // Sprawdzenie, czy usługa istnieje
    $stmt = $pdo->prepare("SELECT id_uslugi FROM oferta_uslug WHERE id_uslugi = ?");
    $stmt->execute([$id_uslugi]);
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$offer) {
        die("Nie znaleziono usługi do usunięcia.");
    }

    // Usunięcie usługi z bazy danych
    $stmt = $pdo->prepare("DELETE FROM oferta_uslug WHERE id_uslugi = ?");
    $stmt->execute([$id_uslugi]);

    echo "<script>alert('Usługa została pomyślnie usunięta.'); window.location.href = 'admin_manage_offers.php';</script>";
} catch (PDOException $e) {
    die("Błąd: " . $e->getMessage());
}
?>


