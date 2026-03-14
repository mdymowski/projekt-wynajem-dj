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

if (isset($_GET['id_user'])) {
    $id_user = $_GET['id_user'];

    try {
        // Sprawdzenie, czy użytkownik istnieje
        $stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE id_user = ?");
        $stmt->execute([$id_user]);
        $user = $stmt->fetch();

        if (!$user) {
            // Użytkownik nie istnieje
            echo "<script>alert('Nie znaleziono użytkownika.'); window.location.href='admin_edit_user.php';</script>";
            exit();
        }

        // Sprawdzenie, czy użytkownik jest administratorem
        if ($user['id_uprawnienia'] == 1) {
            echo "<script>alert('Nie można usunąć administratora.'); window.location.href='admin_edit_user.php';</script>";
            exit();
        }

        // Usunięcie użytkownika z bazy danych
        $stmt = $pdo->prepare("DELETE FROM uzytkownicy WHERE id_user = ?");
        $stmt->execute([$id_user]);

        // Przekierowanie po usunięciu użytkownika
        header("Location: admin_edit_user.php");

    } catch (Exception $e) {
        echo "Błąd podczas usuwania użytkownika: " . $e->getMessage();
        exit(); // Zapewnia zakończenie skryptu po wyświetleniu błędu
    }
} else {
    echo "<script>alert('Nie podano ID użytkownika.'); window.location.href='admin_edit_user.php';</script>";
    exit();
}
?>
