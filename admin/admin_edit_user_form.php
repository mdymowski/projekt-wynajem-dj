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

// Pobranie ID użytkownika do edycji
if (!isset($_GET['id_user'])) {
    echo "Brak ID użytkownika.";
    exit();
}

$id_user = $_GET['id_user'];

// Pobranie danych użytkownika
$stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$id_user]);
$user = $stmt->fetch();

if (!$user) {
    echo "Nie znaleziono użytkownika.";
    exit();
}

// Pobranie liczby administratorów
$stmt_admin_count = $pdo->query("SELECT COUNT(*) AS admin_count FROM uzytkownicy WHERE id_uprawnienia = 1");
$admin_count = $stmt_admin_count->fetchColumn();

// Pobranie listy ról z bazy danych
$stmt_roles = $pdo->query("SELECT id_uprawnienia, nazwa_uprawnienia FROM uprawnienia");
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

// Obsługa formularza edycji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobranie danych z formularza
    $imie = trim($_POST['imie']);
    $nazwisko = trim($_POST['nazwisko']);
    $email = trim($_POST['email']);
    $miasto = trim($_POST['miasto']);
    $ulica = trim($_POST['ulica']);
    $nr_domu = trim($_POST['nr_domu']);
    $nr_mieszkania = trim($_POST['nr_mieszkania']);
    $nr_telefonu = trim($_POST['nr_telefonu']);
    $haslo = $_POST['haslo'] ? hash('sha256', trim($_POST['haslo'])) : $user['haslo'];
    $id_uprawnienia = $_POST['id_uprawnienia']; // Administrator może zmieniać rolę użytkownika

    // Walidacja e-maila
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Nieprawidłowy format adresu email.";
        exit();
    }

    // Sprawdzenie, czy e-mail jest już używany przez innego użytkownika
    $stmt = $pdo->prepare("SELECT id_user FROM uzytkownicy WHERE email = ? AND id_user != ?");
    $stmt->execute([$email, $id_user]);
    if ($stmt->fetch()) {
        echo "Ten adres email jest już używany przez innego użytkownika.";
        exit();
    }

    // Sprawdzenie liczby administratorów
    if ($admin_count == 1 && $user['id_uprawnienia'] == 1 && $id_uprawnienia != 1) {
        echo "Nie można zdegradować ostatniego administratora.";
        exit();
    }
    // Aktualizacja danych w bazie
    try {
        $stmt = $pdo->prepare("UPDATE uzytkownicy SET imie = ?, nazwisko = ?, email = ?, miasto = ?, ulica = ?, nr_domu = ?, nr_mieszkania = ?, nr_telefonu = ?, haslo = ?, id_uprawnienia = ? WHERE id_user = ?");
        $stmt->execute([$imie, $nazwisko, $email, $miasto, $ulica, $nr_domu, $nr_mieszkania, $nr_telefonu, $haslo, $id_uprawnienia, $id_user]);
        
        echo "Dane użytkownika zostały zaktualizowane!";
        // Przekierowanie po edycji użytkownika
        header("Location: admin_edit_user.php");
        exit();
    } catch (Exception $e) {
        echo "Wystąpił błąd: " . $e->getMessage();
    }
}

// Obsługa usunięcia użytkownika
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    try {
        // Usunięcie użytkownika
        $stmt = $pdo->prepare("DELETE FROM uzytkownicy WHERE id_user = ?");
        $stmt->execute([$id_user]);

        echo "Użytkownik został usunięty!";
    } catch (Exception $e) {
        echo "Błąd podczas usuwania użytkownika: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Użytkownika</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Edytuj dane użytkownika</h1>
        
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
            
            <label for="id_uprawnienia">Rola użytkownika:</label>
            <select id="id_uprawnienia" name="id_uprawnienia" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id_uprawnienia'] ?>" <?= $user['id_uprawnienia'] == $role['id_uprawnienia'] ? 'selected' : '' ?>><?= htmlspecialchars($role['nazwa_uprawnienia']) ?></option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="button">Zaktualizuj dane</button>
        </form>
        <a href="admin_delete_user.php?id_user=<?= $user['id_user'] ?>" class="button" onclick="return confirm('Czy na pewno chcesz usunąć tego użytkownika?');">Usuń użytkownika</a>
        <a href="admin_edit_user.php" class="button">Powrót do edycji użytkowników</a>
        <a href="admin_panel.php" class="button">Powrót do panelu administratora</a>
    </div>
</body>
</html>


