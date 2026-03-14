<?php
include 'config.php';

// Sprawdzenie, czy administrator jest zalogowany
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login_register.php"); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
    exit();
}

// sprawdzenie czy admin
$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT id_uprawnienia FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jeśli użytkownik nie jest administratorem, przekierowanie
if (!$user || !in_array($user['id_uprawnienia'], [1])) {
    header("Location: user_dashboard.php");
    exit();
}

if (isset($_GET['id_rezerwacji'])) {
    $id_rezerwacji = $_GET['id_rezerwacji'];

    try {
        // Zatwierdzenie rezerwacji (zmiana statusu na 1)
        $stmt = $pdo->prepare("UPDATE rezerwacja SET status = 1 WHERE id_rezerwacji = ?");
        $stmt->execute([$id_rezerwacji]);

        // Przekierowanie po usunięciu opinii
        header("Location: admin_orders.php");

    } catch (Exception $e) {
        echo "Błąd podczas zatwierdzania zamówienia: " . $e->getMessage();
    }
}
?>
