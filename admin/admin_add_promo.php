<?php
include '../config.php';

// Sprawdzenie, czy administrator jest zalogowany
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login_register.php"); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany
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

// Pobieranie usług dla opcji wyboru
$stmt_services = $pdo->query("SELECT id_uslugi, nazwa FROM oferta_uslug");
$services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

// Obsługa formularza dodawania promocji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nazwa = trim($_POST['nazwa']);
    $opis = trim($_POST['opis']);
    $rabat = floatval($_POST['rabat']);
    $data_rozpoczecia = $_POST['data_rozpoczecia'];
    $data_zakonczenia = $_POST['data_zakonczenia'];
    $id_uslugi = $_POST['id_uslugi'] === 'all' ? null : intval($_POST['id_uslugi']);

    if (empty($nazwa) || empty($opis) || $rabat <= 0 || empty($data_rozpoczecia) || empty($data_zakonczenia)) {
        $error = "Wszystkie pola muszą być wypełnione, a rabat musi być większy od zera.";
    } else {
        try {
            // Dodanie nowej promocji do bazy danych
            $stmt = $pdo->prepare("INSERT INTO promocje (nazwa, opis, rabat, data_rozpoczecia, data_zakonczenia) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nazwa, $opis, $rabat, $data_rozpoczecia, $data_zakonczenia]);

            // Pobranie ID nowo dodanej promocji
            $id_promocji = $pdo->lastInsertId();

            // Jeśli promocja dotyczy konkretnej usługi, aktualizacja tabeli oferta_uslug
            if ($id_uslugi !== null) {
                $stmt = $pdo->prepare("UPDATE oferta_uslug SET id_promocji = ? WHERE id_uslugi = ?");
                $stmt->execute([$id_promocji, $id_uslugi]);
            }

            $success = "Nowa promocja została pomyślnie dodana.";
        } catch (PDOException $e) {
            $error = "Błąd podczas dodawania promocji: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj promocję</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Dodaj nową promocję</h1>

        <?php if (isset($error)): ?>
            <div class="error"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"> <?= htmlspecialchars($success) ?> </div>
        <?php endif; ?>

        <form method="POST">
            <label for="nazwa">Nazwa promocji:</label>
            <input type="text" id="nazwa" name="nazwa" required>

            <label for="opis">Opis promocji:</label>
            <textarea id="opis" name="opis" rows="5" required></textarea>

            <label for="rabat">Rabat (%):</label>
            <input type="number" id="rabat" name="rabat" step="0.01" required>

            <label for="data_rozpoczecia">Data rozpoczęcia:</label>
            <input type="date" id="data_rozpoczecia" name="data_rozpoczecia" required>

            <label for="data_zakonczenia">Data zakończenia:</label>
            <input type="date" id="data_zakonczenia" name="data_zakonczenia" required>

            <label for="id_uslugi">Dotyczy usługi:</label>
            <select id="id_uslugi" name="id_uslugi">
                <option value="all">Wszystkie usługi</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id_uslugi'] ?>"><?= htmlspecialchars($service['nazwa']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="button">Dodaj promocję</button>
        </form>

        <a href="admin_promo.php" class="button">Powrót do zarządzania promocjami</a>
    </div>
</body>
</html>


