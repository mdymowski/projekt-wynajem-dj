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

// Pobranie ID pytania do usunięcia
if (!isset($_GET['id_faq']) || !is_numeric($_GET['id_faq'])) {
    die("Nieprawidłowy identyfikator pytania.");
}

$id_faq = intval($_GET['id_faq']);

try {
    // Sprawdzenie, czy pytanie istnieje
    $stmt = $pdo->prepare("SELECT id_faq FROM faq WHERE id_faq = ?");
    $stmt->execute([$id_faq]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        die("Nie znaleziono pytania do usunięcia.");
    }

    // Usunięcie pytania z bazy danych
    $stmt = $pdo->prepare("DELETE FROM faq WHERE id_faq = ?");
    $stmt->execute([$id_faq]);

    echo "<script>alert('Pytanie zostało pomyślnie usunięte.'); window.location.href = 'admin_manage_questions.php';</script>";
} catch (PDOException $e) {
    die("Błąd: " . $e->getMessage());
}
?>


