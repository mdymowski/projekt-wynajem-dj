<?php
session_start();
include 'config.php';

if (isset($_POST['register_user'])) {
    $email = trim($_POST['email']);
    $login = trim($_POST['login']);
    $haslo = trim($_POST['haslo']);
    $imie = trim($_POST['imie']);
    $nazwisko = trim($_POST['nazwisko']);
    $miasto = trim($_POST['miasto']);
    $ulica = trim($_POST['ulica']);
    $nr_domu = trim($_POST['nr_domu']);
    $nr_mieszkania = trim($_POST['nr_mieszkania']);
    $nr_telefonu = trim($_POST['nr_telefonu']);

    try {
        // Rejestracja nowego użytkownika
        $stmt = $pdo->prepare("INSERT INTO uzytkownicy (login, haslo, email, imie, nazwisko, miasto, ulica, nr_domu, nr_mieszkania, nr_telefonu, id_uprawnienia) 
                               VALUES (?, SHA2(?, 256), ?, ?, ?, ?, ?, ?, ?, ?, 3)");

        // Jeśli nr_mieszkania jest pusty, ustaw NULL
        if (empty($nr_mieszkania)) {
            $nr_mieszkania = null;
        }

        $stmt->execute([$login, $haslo, $email, $imie, $nazwisko, $miasto, $ulica, $nr_domu, $nr_mieszkania, $nr_telefonu]);
        $success = "Rejestracja zakończona sukcesem! Możesz się teraz zalogować.";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Błąd klucza unikalnego
            $error = "Podany login lub e-mail jest już zajęty.";
        } else {
            $error = "Błąd podczas rejestracji: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Rejestracja</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required>
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" required>
            <label for="haslo">Hasło:</label>
            <input type="password" id="haslo" name="haslo" required>
            <label for="imie">Imię:</label>
            <input type="text" id="imie" name="imie" required>
            <label for="nazwisko">Nazwisko:</label>
            <input type="text" id="nazwisko" name="nazwisko" required>
            <label for="miasto">Miasto:</label>
            <input type="text" id="miasto" name="miasto" required>
            <label for="ulica">Ulica:</label>
            <input type="text" id="ulica" name="ulica" required>
            <label for="nr_domu">Numer domu:</label>
            <input type="text" id="nr_domu" name="nr_domu" required>
            <label for="nr_mieszkania">Numer mieszkania (opcjonalnie):</label>
            <input type="text" id="nr_mieszkania" name="nr_mieszkania">
            <label for="nr_telefonu">Numer telefonu:</label>
            <input type="text" id="nr_telefonu" name="nr_telefonu" required>
            <button type="submit" name="register_user">Zarejestruj się</button>
        </form>
        <a href="index.php" class="button">Powrót do strony głównej</a>
    </div>
</body>
</html>


