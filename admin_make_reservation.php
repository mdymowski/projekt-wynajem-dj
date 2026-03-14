<?php
include 'config.php';

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login_register.php"); // Przekierowanie na stronę logowania, jeśli nie jest administratorem
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobieranie danych z formularza i ich walidacja
    $service = trim($_POST['service']);
    $date = trim($_POST['date']);
    $playlist_link = trim($_POST['playlist_link']); // Pobranie linku do playlisty
    $selected_user = intval($_POST['user']);

    // Walidacja daty rezerwacji (czy jest w przyszłości)
    $current_date = date('Y-m-d');
    if ($date < $current_date) {
        echo "Data rezerwacji nie może być w przeszłości.";
        exit();
    }

    try {
        // Pobranie ID usługi
        $stmt = $pdo->prepare("SELECT id_uslugi FROM oferta_uslug WHERE nazwa = ?");
        $stmt->execute([$service]);
        $service_id = $stmt->fetchColumn();

        if (!$service_id) {
            throw new Exception("Nie znaleziono wybranej usługi.");
        }

        // Dodanie rezerwacji do bazy danych z domyślnym statusem '0' (oczekująca)
        $stmt = $pdo->prepare("INSERT INTO rezerwacja (id_user, termin, id_uslugi, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$selected_user, $date, $service_id, 0]); // Status 0 oznacza oczekującą rezerwację

        // Pobranie ID ostatniej dodanej rezerwacji
        $reservation_id = $pdo->lastInsertId();

        // Dodanie linku do playlisty (jeśli został podany) do tabeli 'playlisty'
        if (!empty($playlist_link)) {
            $stmt = $pdo->prepare("INSERT INTO playlisty (id_rezerwacji, link_playlista) VALUES (?, ?)");
            $stmt->execute([$reservation_id, $playlist_link]);
        }

        echo "Zamówienie zostało pomyślnie dodane!";
    } catch (Exception $e) {
        // Obsługa błędów
        echo "Wystąpił błąd podczas dodawania zamówienia: " . $e->getMessage();
    }
}

try {
    // Pobieranie danych z bazy do wyświetlenia ofert i promocji
    $stmt_offers = $pdo->query("SELECT nazwa, cena, opis FROM oferta_uslug");
    $offers = $stmt_offers->fetchAll(PDO::FETCH_ASSOC);

    $stmt_promotions = $pdo->query("SELECT nazwa, opis, rabat FROM promocje WHERE NOW() BETWEEN data_rozpoczecia AND data_zakonczenia");
    $promotions = $stmt_promotions->fetchAll(PDO::FETCH_ASSOC);

    // Pobranie listy usług dla formularza
    $stmt_services = $pdo->query("SELECT nazwa FROM oferta_uslug");
    $services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

    // Pobranie listy użytkowników
    $stmt_users = $pdo->query("SELECT id_user, CONCAT(imie, ' ', nazwisko) AS fullname FROM uzytkownicy");
    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Błąd podczas pobierania danych: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Zamówienie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Formularz Dodawania Zamówienia</h1>
        <h2>Nasze Usługi</h2>
            <div class="offers">
                <?php foreach ($offers as $offer): ?>
                    <div class="offer">
                        <h3><?= htmlspecialchars($offer['nazwa']) ?></h3>
                        <p><?= htmlspecialchars($offer['opis']) ?></p>
                        <p><strong>Cena:</strong> <?= number_format($offer['cena'], 2) ?> PLN</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section>
            <h2>Promocje</h2>
            <?php if (!empty($promotions)): ?>
                <ul>
                    <?php foreach ($promotions as $promotion): ?>
                        <li>
                            <strong><?= htmlspecialchars($promotion['nazwa']) ?>:</strong> 
                            <?= htmlspecialchars($promotion['opis']) ?> 
                            (<?= htmlspecialchars($promotion['rabat']) ?>% zniżki)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Obecnie brak aktywnych promocji.</p>
            <?php endif; ?>
        </section>
        <br>
        <form method="POST">
            <label for="user">Wybierz użytkownika:</label>
            <select id="user" name="user" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?= htmlspecialchars($user['id_user']) ?>">
                        <?= htmlspecialchars($user['fullname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="service">Wybierz usługę:</label>
            <select id="service" name="service" required>
                <?php foreach ($services as $service): ?>
                    <option value="<?= htmlspecialchars($service['nazwa']) ?>">
                        <?= htmlspecialchars($service['nazwa']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="date">Data realizacji zamówienia:</label>
            <input type="date" id="date" name="date" required>

            <label for="playlist_link">Link do playlisty (opcjonalnie):</label>
            <input type="url" id="playlist_link" name="playlist_link" placeholder="https://example.com/playlist">

            <button type="submit" class="button">Dodaj Zamówienie</button>
        </form>
        <a href="admin_panel.php" class="button">Powrót do panelu administratora</a>
    </div>
</body>
</html>
