<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $haslo = $_POST['haslo'];

    // Zapytanie do bazy danych, aby sprawdzić, czy użytkownik istnieje
    $stmt = $pdo->prepare("SELECT id_user, haslo, id_uprawnienia FROM uzytkownicy WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    // Jeśli użytkownik istnieje i hasło jest poprawne
    if ($user && hash('sha256', $haslo) === $user['haslo']) {
        // Zaloguj użytkownika, jeżeli hasło jest poprawne
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['id_uprawnienia'] = $user['id_uprawnienia'];

        // Jeżeli użytkownik jest administratorem
        if ($user['id_uprawnienia'] == 1) {
            $_SESSION['admin'] = $user['id_user'];
            header("Location: admin_panel.php");
        } elseif ($user['id_uprawnienia'] == 2) {
            // Przekierowanie do panelu moderatora
            header("Location: moderator_dashboard.php");
        } else {
            // Przekierowanie do panelu użytkownika
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        $error = "Nieprawidłowy login lub hasło.";
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
        <h1>Logowanie</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" required>
            <label for="haslo">Hasło:</label>
            <input type="password" id="haslo" name="haslo" required>
            <button type="submit">Zaloguj</button>
        </form>
        <a href="index.php" class="button">Powrót do strony głównej</a>
    </div>
</body>
</html>
