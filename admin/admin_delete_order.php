<?php
include '../config.php';

// Sprawdzenie, czy administrator jest zalogowany
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login_register.php"); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
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

if (isset($_GET['id_rezerwacji'])) {
    $id_rezerwacji = $_GET['id_rezerwacji'];

    try {
        // Sprawdzenie, czy rezerwacja ma przypisaną playlistę
        $stmt = $pdo->prepare("SELECT id_rezerwacji FROM playlisty WHERE id_rezerwacji = ?");
        $stmt->execute([$id_rezerwacji]);
        $playlist = $stmt->fetch();

        // Jeśli istnieje powiązana playlista, usuwamy ją
        if ($playlist) {
            $stmt = $pdo->prepare("DELETE FROM playlisty WHERE id_rezerwacji = ?");
            $stmt->execute([$id_rezerwacji]);
        }

        // Usunięcie rezerwacji z bazy danych
        $stmt = $pdo->prepare("DELETE FROM rezerwacja WHERE id_rezerwacji = ?");
        $stmt->execute([$id_rezerwacji]);

        // Przekierowanie po usunięciu rezerwacji
        header("Location: admin_orders.php");
    } catch (Exception $e) {
        echo "Błąd podczas usuwania zamówienia: " . $e->getMessage();
    }
}
?>


