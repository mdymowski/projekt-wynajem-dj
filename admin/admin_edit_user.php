<?php
include '../config.php';

// Sprawdzenie, czy administrator jest zalogowany
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login_register.php"); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany lub nie ma uprawnień
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

// Pobieranie listy użytkowników
$stmt = $pdo->query("SELECT id_user, imie, nazwisko, email FROM uzytkownicy");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wybór użytkownika do edycji</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Wybierz użytkownika do edycji lub dodaj nowego</h1>
        
        <form method="GET" action="admin_edit_user_form.php">
            <label for="user_id">Wybierz użytkownika:</label>
            <select id="user_id" name="id_user" required>
                <option value="">-- Wybierz użytkownika --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id_user'] ?>"><?= htmlspecialchars($user['imie']) ?> <?= htmlspecialchars($user['nazwisko']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="button">Edytuj użytkownika</button>
        </form>
        <a href="admin_add_user.php" class="button">Dodaj nowego użytkownika</a>
        <br>
        <a href="admin_panel.php" class="button">Powrót do panelu administratora</a>
    </div>
</body>
</html>


