<?php
session_start();
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['id_user'])) {
    header('Location: login_register.php');
    exit();
}

$user_id = $_SESSION['id_user'];

// Sprawdzamy, czy przekazano poprawny identyfikator rezerwacji
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $reservation_id = $_GET['id'];

    // Pobranie szczegółów rezerwacji
    $stmt = $pdo->prepare("SELECT id_rezerwacji, termin, id_uslugi FROM rezerwacja WHERE id_rezerwacji = ? AND id_user = ?");
    $stmt->execute([$reservation_id, $user_id]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
        echo "Rezerwacja nie została znaleziona.";
        exit();
    }

    // Pobranie dostępnych usług
    $stmt = $pdo->prepare("SELECT id_uslugi, nazwa FROM oferta_uslug");
    $stmt->execute();
    $services = $stmt->fetchAll();

    // Pobranie linku do playlisty (jeśli istnieje)
    $stmt = $pdo->prepare("SELECT link_playlista FROM playlisty WHERE id_rezerwacji = ?");
    $stmt->execute([$reservation_id]);
    $playlist = $stmt->fetch();

    // Przetwarzanie formularza
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_date = $_POST['date'];
        $new_service = $_POST['service'];
        $new_playlist_link = $_POST['playlist_link']; // Nowy link do playlisty

        // Aktualizacja rezerwacji
        $stmt = $pdo->prepare("UPDATE rezerwacja SET termin = ?, id_uslugi = ? WHERE id_rezerwacji = ?");
        $stmt->execute([$new_date, $new_service, $reservation_id]);

        // Jeżeli link do playlisty został usunięty (pole jest puste)
        if (empty($new_playlist_link) && $playlist) {
            // Usunięcie powiązanej playlisty z bazy
            $stmt = $pdo->prepare("DELETE FROM playlisty WHERE id_rezerwacji = ?");
            $stmt->execute([$reservation_id]);
        }

        // Jeżeli nowy link do playlisty jest podany
        if (!empty($new_playlist_link)) {
            if ($playlist) {
                // Jeżeli playlista już istnieje, zaktualizuj link
                $stmt = $pdo->prepare("UPDATE playlisty SET link_playlista = ? WHERE id_rezerwacji = ?");
                $stmt->execute([$new_playlist_link, $reservation_id]);
            } else {
                // Jeżeli playlisty nie było, dodaj nową
                $stmt = $pdo->prepare("INSERT INTO playlisty (id_rezerwacji, link_playlista) VALUES (?, ?)");
                $stmt->execute([$reservation_id, $new_playlist_link]);
            }
        }

        echo "Rezerwacja została zaktualizowana.";
        header("Location: user/user_dashboard.php");
        exit();
    }
} else {
    echo "Błąd: Nieprawidłowy identyfikator rezerwacji.";
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moje zamówienia</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Formularz edycji rezerwacji -->
    <div class="container">
        <form method="POST">
            <h2>Edytuj Rezerwację</h2>

            <label for="date">Nowy termin:</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($reservation['termin']) ?>" required>

            <label for="service">Nowa usługa:</label>
            <select id="service" name="service" required>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id_uslugi'] ?>" <?= $service ['id_uslugi'] == $reservation['id_uslugi'] ? 'selected': '' ?>>
                    <?= htmlspecialchars($service['nazwa']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Pole do edycji linku do playlisty -->
            <label for="playlist_link">Link do playlisty (opcjonalnie):</label>
            <input type="url" id="playlist_link" name="playlist_link" value="<?= htmlspecialchars($playlist['link_playlista'] ?? '') ?>" placeholder="https://example.com/playlist">

            <button type="submit" class="btn">Zaktualizuj rezerwację</button>
        </form>
        <a href="user/user_dashboard.php" class="button">Powrót do Twoich zamówień</a>
    </div>
</body>
</html>


