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

// Dodawanie nowego użytkownika
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    // Pobieranie danych z formularza
    $login = trim($_POST['login']);
    $haslo = hash('sha256', trim($_POST['haslo']));
    $email = trim($_POST['email']);
    $imie = trim($_POST['imie']);
    $nazwisko = trim($_POST['nazwisko']);
    $uprawnienia = trim($_POST['uprawnienia']);

    // Walidacja emaila
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Nieprawidłowy format adresu email.";
        exit();
    }

    try {
        // Sprawdzenie, czy użytkownik z tym emailem już istnieje
        $stmt = $pdo->prepare("SELECT id_user FROM uzytkownicy WHERE email = ?");
        $stmt->execute([$email]);
        $user_exists = $stmt->fetch();

        if ($user_exists) {
            echo "Użytkownik o tym adresie email już istnieje.";
        } else {
            // Dodanie nowego użytkownika do bazy danych
            $stmt = $pdo->prepare("INSERT INTO uzytkownicy (login, haslo, email, imie, nazwisko, id_uprawnienia) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$login, $haslo, $email, $imie, $nazwisko, $uprawnienia]);

            echo "Nowy użytkownik został dodany!";
        }
    } catch (Exception $e) {
        echo "Błąd podczas dodawania użytkownika: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administratora - Dodaj Użytkownika</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Panel Administratora - Dodaj Nowego Użytkownika</h1>
        
        <!-- Formularz do dodawania nowego użytkownika -->
        <form method="POST">
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" required>

            <label for="haslo">Hasło:</label>
            <input type="password" id="haslo" name="haslo" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="imie">Imię:</label>
            <input type="text" id="imie" name="imie" required>

            <label for="nazwisko">Nazwisko:</label>
            <input type="text" id="nazwisko" name="nazwisko" required>

            <label for="uprawnienia">Uprawnienia:</label>
            <select id="uprawnienia" name="uprawnienia" required>
                <option value="1">Administrator</option>
                <option value="2">Moderator</option>
                <option value="3">Użytkownik</option>
            </select>

            <button type="submit" name="add_user" class="button">Dodaj Użytkownika</button>
        </form>

        <a href="admin_edit_user.php" class="button">Powrót do edycji użytkowników</a>
        <a href="admin_panel.php" class="button">Powrót do panelu administratora</a>
    </div>
</body>
</html>
