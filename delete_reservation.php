<?php
session_start();
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['id_user'])) {
    header('Location: login_register.php');
    exit();
}

$user_id = $_SESSION['id_user'];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $reservation_id = $_GET['id'];

    // Sprawdzanie, czy rezerwacja należy do zalogowanego użytkownika i czy nie jest zatwierdzona
    $stmt = $pdo->prepare("SELECT id_rezerwacji, status FROM rezerwacja WHERE id_rezerwacji = ? AND id_user = ?");
    $stmt->execute([$reservation_id, $user_id]);
    $reservation = $stmt->fetch();

    if ($reservation) {
        if ($reservation['status'] == 1) { // Sprawdzanie, czy rezerwacja jest zatwierdzona
            echo "<script>alert('Nie można usunąć zatwierdzonej rezerwacji! Skontaktuj się z administratorem.'); window.location.href = 'user/user_dashboard.php';</script>";
            exit();
        }

        // Usuwanie powiązanej playlisty, jeśli istnieje
        $stmt = $pdo->prepare("DELETE FROM playlisty WHERE id_rezerwacji = ?");
        $stmt->execute([$reservation_id]);

        // Usuwanie rezerwacji
        $stmt = $pdo->prepare("DELETE FROM rezerwacja WHERE id_rezerwacji = ?");
        $stmt->execute([$reservation_id]);

        echo "<script>alert('Rezerwacja i powiązana playlista zostały usunięte.'); window.location.href = 'user/user_dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Błąd: Nie znaleziono rezerwacji.'); window.location.href = 'user/user_dashboard.php';</script>";
    }
} else {
    echo "<script>alert('Błąd: Nieprawidłowy identyfikator rezerwacji.'); window.location.href = 'user/user_dashboard.php';</script>";
}
?>


