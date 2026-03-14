<?php
// Zakładając, że masz już połączenie z bazą danych (config.php)
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: login_register.php'); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
    exit();
}

$id_user = $_SESSION['id_user'];

// Pobranie danych użytkownika z bazy
$stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$id_user]);
$user = $stmt->fetch();

// Sprawdzenie, czy dane zostały znalezione
if (!$user) {
    echo "Nie znaleziono użytkownika.";
    exit();
}

// Obsługa formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sprawdzamy, czy użytkownik chce zaktualizować dane
    if (isset($_POST['update_profile'])) {
        $imie = trim($_POST['imie']);
        $nazwisko = trim($_POST['nazwisko']);
        $email = trim($_POST['email']);
        $miasto = trim($_POST['miasto']);
        $ulica = trim($_POST['ulica']);
        $nr_domu = trim($_POST['nr_domu']);
        $nr_mieszkania = trim($_POST['nr_mieszkania']);
        $nr_telefonu = trim($_POST['nr_telefonu']);
        $haslo = $_POST['haslo'] ? hash('sha256', trim($_POST['haslo'])) : $user['haslo'];

        // Walidacja e-maila
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Nieprawidłowy format adresu email.";
            exit();
        }

        // Zaktualizowanie danych w bazie
        try {
            $stmt = $pdo->prepare("UPDATE uzytkownicy SET imie = ?, nazwisko = ?, email = ?, miasto = ?, ulica = ?, nr_domu = ?, nr_mieszkania = ?, nr_telefonu = ?, haslo = ? WHERE id_user = ?");
            $stmt->execute([$imie, $nazwisko, $email, $miasto, $ulica, $nr_domu, $nr_mieszkania, $nr_telefonu, $haslo, $id_user]);

            echo "Dane zostały zaktualizowane!";
        } catch (Exception $e) {
            echo "Wystąpił błąd: " . $e->getMessage();
        }
    }

    // Obsługa usunięcia konta
    if (isset($_POST['delete_account'])) {
        try {
            // Usunięcie użytkownika
            $stmt = $pdo->prepare("DELETE FROM uzytkownicy WHERE id_user = ?");
            $stmt->execute([$id_user]);

            // Zakończenie sesji i przekierowanie na stronę główną
            session_unset();
            session_destroy();
            header("Location: index.php"); // Przekierowanie na stronę główną
            exit();
        } catch (Exception $e) {
            echo "Błąd podczas usuwania użytkownika: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Profil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edytuj swoje dane</h1>
        
        <!-- Formularz edycji danych -->
        <form method="POST">
            <label for="imie">Imię:</label>
            <input type="text" id="imie" name="imie" value="<?= htmlspecialchars($user['imie']) ?>" required>

            <label for="nazwisko">Nazwisko:</label>
            <input type="text" id="nazwisko" name="nazwisko" value="<?= htmlspecialchars($user['nazwisko']) ?>" required>
            
            <label for="email">Adres email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            
            <label for="miasto">Miasto:</label>
            <input type="text" id="miasto" name="miasto" value="<?= htmlspecialchars($user['miasto']) ?>" required>
            
            <label for="ulica">Ulica:</label>
            <input type="text" id="ulica" name="ulica" value="<?= htmlspecialchars($user['ulica']) ?>" required>
            
            <label for="nr_domu">Numer domu:</label>
            <input type="number" id="nr_domu" name="nr_domu" value="<?= htmlspecialchars($user['nr_domu']) ?>" required>
            
            <label for="nr_mieszkania">Numer mieszkania:</label>
            <input type="number" id="nr_mieszkania" name="nr_mieszkania" value="<?= htmlspecialchars($user['nr_mieszkania']) ?>">
            
            <label for="nr_telefonu">Numer telefonu:</label>
            <input type="text" id="nr_telefonu" name="nr_telefonu" value="<?= htmlspecialchars($user['nr_telefonu']) ?>" required>
            
            <label for="haslo">Hasło (pozostaw puste, jeśli nie chcesz zmieniać):</label>
            <input type="password" id="haslo" name="haslo">
            
            <button type="submit" name="update_profile" class="button">Zaktualizuj dane</button>
        </form>

        <!-- Formularz do usunięcia konta -->
        <form method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć swoje konto?');">
            <button type="submit" name="delete_account" class="button">Usuń konto</button>
        </form>

        <a href="user_dashboard.php" class="button">Powrót do panelu użytkownika</a>
    </div>
</body>
</html>
