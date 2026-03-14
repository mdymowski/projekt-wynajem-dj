<?php
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login_register.php'); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
    exit();
}

$id_user = $_SESSION['id_user'];

// Pobranie ID opinii do usunięcia
if (!isset($_GET['id_opinii'])) {
    echo "Brak ID opinii.";
    exit();
}

$id_opinii = $_GET['id_opinii'];

// Usuwanie opinii
try {
    $stmt = $pdo->prepare("DELETE FROM opinie_klientow WHERE id_opinii = ? AND id_user = ?");
    $stmt->execute([$id_opinii, $id_user]);

    // Przekierowanie po usunięciu opinii
    header("Location: user_opinion.php");
    exit();
} catch (Exception $e) {
    echo "Błąd podczas usuwania opinii: " . $e->getMessage();
    exit();
}
?>
