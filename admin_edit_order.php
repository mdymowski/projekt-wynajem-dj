<?php
include 'config.php';

// Sprawdzenie, czy administrator jest zalogowany
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login_register.php"); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany lub nie ma uprawnień
    exit();
}

// Sprawdzenie, czy użytkownik jest administratorem
$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT id_uprawnienia FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jeśli użytkownik nie jest administratorem, przekierowanie
if (!$user || !in_array($user['id_uprawnienia'], [1])) {
    header("Location: user_dashboard.php");
    exit();
}

// Pobranie ID zamówienia do edycji
if (!isset($_GET['id_rezerwacji'])) {
    echo "Brak ID zamówienia.";
    exit();
}

$id_rezerwacji = $_GET['id_rezerwacji'];

// Pobranie danych zamówienia
$stmt = $pdo->prepare("SELECT r.*, u.imie, u.nazwisko, u.email, o.nazwa AS usluga, p.link_playlista 
                       FROM rezerwacja r
                       JOIN uzytkownicy u ON r.id_user = u.id_user
                       JOIN oferta_uslug o ON r.id_uslugi = o.id_uslugi
                       LEFT JOIN playlisty p ON r.id_rezerwacji = p.id_rezerwacji
                       WHERE r.id_rezerwacji = ?");
$stmt->execute([$id_rezerwacji]);
$order = $stmt->fetch();

if (!$order) {
    echo "Nie znaleziono zamówienia.";
    exit();
}

// Pobranie dostępnych usług
$stmt_services = $pdo->query("SELECT id_uslugi, nazwa FROM oferta_uslug");
$services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

// Obsługa formularza edycji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $termin = trim($_POST['termin']);
    $usluga = $_POST['usluga'];
    $playlist_link = trim($_POST['playlist_link']); // Pobranie linku do playlisty

    // Walidacja daty rezerwacji
    $current_date = date('Y-m-d');
    if ($termin < $current_date) {
        echo "Data rezerwacji nie może być w przeszłości.";
        exit();
    }

    try {
        // Aktualizacja zamówienia w bazie
        $stmt = $pdo->prepare("UPDATE rezerwacja SET termin = ?, id_uslugi = ? WHERE id_rezerwacji = ?");
        $stmt->execute([$termin, $usluga, $id_rezerwacji]);

        // Jeśli podano link do playlisty
        if ($playlist_link) {
            // Sprawdzenie, czy playlista istnieje, a jeśli tak to aktualizacja
            $stmt = $pdo->prepare("SELECT id_rezerwacji FROM playlisty WHERE id_rezerwacji = ?");
            $stmt->execute([$id_rezerwacji]);
            $existing_playlist = $stmt->fetch();

            if ($existing_playlist) {
                // Jeśli playlista już istnieje, aktualizujemy link
                $stmt = $pdo->prepare("UPDATE playlisty SET link_playlista = ? WHERE id_rezerwacji = ?");
                $stmt->execute([$playlist_link, $id_rezerwacji]);
            } else {
                // Jeśli playlisty nie ma, dodajemy nową
                $stmt = $pdo->prepare("INSERT INTO playlisty (id_rezerwacji, link_playlista) VALUES (?, ?)");
                $stmt->execute([$id_rezerwacji, $playlist_link]);
            }
        } else {
            // Jeśli link jest pusty, usuwamy istniejący link do playlisty
            $stmt = $pdo->prepare("DELETE FROM playlisty WHERE id_rezerwacji = ?");
            $stmt->execute([$id_rezerwacji]);
        }

        echo "Zamówienie zostało zaktualizowane!";
        // Przekierowanie po edycji zamówienia
        header("Location: admin_orders.php");

    } catch (Exception $e) {
        echo "Wystąpił błąd: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj zamówienie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edytuj zamówienie</h1>
        
        <form method="POST">
            <label for="imie">Imię:</label>
            <input type="text" id="imie" value="<?= htmlspecialchars($order['imie']) ?>" disabled>

            <label for="nazwisko">Nazwisko:</label>
            <input type="text" id="nazwisko" value="<?= htmlspecialchars($order['nazwisko']) ?>" disabled>

            <label for="email">Email:</label>
            <input type="email" id="email" value="<?= htmlspecialchars($order['email']) ?>" disabled>

            <label for="termin">Nowa data rezerwacji:</label>
            <input type="date" id="termin" name="termin" value="<?= htmlspecialchars($order['termin']) ?>" required>

            <label for="usluga">Wybierz usługę:</label>
            <select id="usluga" name="usluga" required>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id_uslugi'] ?>" <?= $order['id_uslugi'] == $service['id_uslugi'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($service['nazwa']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="playlist_link">Link do playlisty:</label>
            <input type="url" id="playlist_link" name="playlist_link" value="<?= htmlspecialchars($order['link_playlista'] ?? '') ?>" placeholder="Wprowadź link do playlisty">

            <button type="submit" class="button">Zaktualizuj zamówienie</button>
        </form>

        <a href="admin_orders.php" class="button">Powrót do listy zamówień</a>
    </div>
</body>
</html>
