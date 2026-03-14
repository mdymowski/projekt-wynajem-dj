<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login_register.php"); // Przekierowanie do strony logowania, jeśli nie jest zalogowany
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

// Pobranie ID roli do edycji
if (!isset($_GET['id_uprawnienia']) || !is_numeric($_GET['id_uprawnienia'])) {
    die("Nieprawidłowy identyfikator roli.");
}

$id_uprawnienia = intval($_GET['id_uprawnienia']);

try {
    // Pobranie danych roli
    $stmt = $pdo->prepare("SELECT nazwa_uprawnienia FROM uprawnienia WHERE id_uprawnienia = ?");
    $stmt->execute([$id_uprawnienia]);
    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$role) {
        die("Nie znaleziono roli do edycji.");
    }

    // Obsługa formularza edycji
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_role_name = trim($_POST['role_name']);

        if (empty($new_role_name)) {
            $error = "Nazwa roli nie może być pusta.";
        } else {
            $stmt = $pdo->prepare("UPDATE uprawnienia SET nazwa_uprawnienia = ? WHERE id_uprawnienia = ?");
            $stmt->execute([$new_role_name, $id_uprawnienia]);

            $success = "Rola została pomyślnie zaktualizowana.";
            $role['nazwa_uprawnienia'] = $new_role_name;
        }
    }
} catch (PDOException $e) {
    die("Błąd: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj rolę</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edytuj rolę</h1>

        <?php if (isset($error)): ?>
            <div class="error"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"> <?= htmlspecialchars($success) ?> </div>
        <?php endif; ?>

        <form method="POST">
            <label for="role_name">Nazwa roli:</label>
            <input type="text" id="role_name" name="role_name" value="<?= htmlspecialchars($role['nazwa_uprawnienia']) ?>" required>

            <button type="submit" class="button">Zaktualizuj rolę</button>
        </form>

        <a href="admin_roles.php" class="button">Powrót do zarządzania rolami</a>
    </div>
</body>
</html>
