<?php
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login_register.php'); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
    exit();
}

$id_user = $_SESSION['id_user'];

// Pobranie ID pytania do usunięcia
if (!isset($_GET['id_faq']) || !is_numeric($_GET['id_faq'])) {
    die("Nieprawidłowy identyfikator pytania.");
}

$id_faq = intval($_GET['id_faq']);

try {
    // Sprawdzenie, czy pytanie należy do zalogowanego użytkownika
    $stmt = $pdo->prepare("SELECT id_faq FROM faq WHERE id_faq = ? AND id_user = ?");
    $stmt->execute([$id_faq, $id_user]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        die("Nie znaleziono pytania lub brak uprawnień do usunięcia.");
    }

    // Usunięcie pytania z bazy danych
    $stmt = $pdo->prepare("DELETE FROM faq WHERE id_faq = ? AND id_user = ?");
    $stmt->execute([$id_faq, $id_user]);

    echo "<script>alert('Pytanie zostało pomyślnie usunięte.'); window.location.href = 'user_questions.php';</script>";
} catch (PDOException $e) {
    die("Błąd: " . $e->getMessage());
}
?>
