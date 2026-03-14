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

// Pobranie ID promocji do usunięcia
if (!isset($_GET['id_promocji']) || !is_numeric($_GET['id_promocji'])) {
    die("Nieprawidłowy identyfikator promocji.");
}

$id_promocji = intval($_GET['id_promocji']);

try {
    // Sprawdzenie, czy promocja istnieje
    $stmt = $pdo->prepare("SELECT id_promocji FROM promocje WHERE id_promocji = ?");
    $stmt->execute([$id_promocji]);
    $promotion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$promotion) {
        die("Nie znaleziono promocji do usunięcia.");
    }

    // Usunięcie powiązania promocji z usługami
    $stmt = $pdo->prepare("UPDATE oferta_uslug SET id_promocji = NULL WHERE id_promocji = ?");
    $stmt->execute([$id_promocji]);

    // Usunięcie promocji z bazy danych
    $stmt = $pdo->prepare("DELETE FROM promocje WHERE id_promocji = ?");
    $stmt->execute([$id_promocji]);

    echo "<script>alert('Promocja została pomyślnie usunięta.'); window.location.href = 'admin_promo.php';</script>";
} catch (PDOException $e) {
    die("Błąd: " . $e->getMessage());
}
?>


