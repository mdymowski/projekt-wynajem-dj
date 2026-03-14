<?php
include 'config.php';

// Sprawdzenie, czy administrator jest zalogowany
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login_register.php"); // Przekierowanie na stronę logowania, jeśli nie jest zalogowany lub nie ma uprawnień
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

// Pobieranie zamówień z bazy
$stmt = $pdo->query("SELECT r.id_rezerwacji, u.imie, u.nazwisko, u.email, r.termin, o.nazwa AS usluga, r.status 
                     FROM rezerwacja r
                     JOIN uzytkownicy u ON r.id_user = u.id_user
                     JOIN oferta_uslug o ON r.id_uslugi = o.id_uslugi");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie linku do playlisty dla każdej rezerwacji
foreach ($orders as $key => $order) {
    // Zapytanie do tabeli playlisty
    $stmt = $pdo->prepare("SELECT link_playlista FROM playlisty WHERE id_rezerwacji = ?");
    $stmt->execute([$order['id_rezerwacji']]);
    $playlist = $stmt->fetch(PDO::FETCH_ASSOC);

    // Sprawdzamy, czy znaleziono link do playlisty
    $orders[$key]['playlist_link'] = $playlist ? $playlist['link_playlista'] : null; // Przypisanie linku lub null
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obsługa rezerwacji</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Lista rezerwacji</h1>
        
        <?php if (empty($orders)): ?>
            <p>Brak rezerwacji do wyświetlenia.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Imię</th>
                        <th>Nazwisko</th>
                        <th>Email</th>
                        <th>Data rezerwacji</th>
                        <th>Usługa</th>
                        <th>Status</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['imie']) ?></td>
                            <td><?= htmlspecialchars($order['nazwisko']) ?></td>
                            <td><?= htmlspecialchars($order['email']) ?></td>
                            <td><?= htmlspecialchars($order['termin']) ?></td>
                            <td><?= htmlspecialchars($order['usluga']) ?></td>
                            <td><?= $order['status'] == 0 ? 'Oczekujące' : 'Zatwierdzone' ?></td>
                            <td>
                                <a href="admin_edit_order.php?id_rezerwacji=<?= $order['id_rezerwacji'] ?>" class="button">Edytuj</a>
                                <a href="admin_delete_order.php?id_rezerwacji=<?= $order['id_rezerwacji'] ?>" class="button" onclick="return confirm('Czy na pewno chcesz usunąć to zamówienie?');">Usuń</a>
                                <?php if ($order['status'] == 0): ?>
                                    <a href="admin_confirm_order.php?id_rezerwacji=<?= $order['id_rezerwacji'] ?>" class="button" onclick="return confirm('Czy na pewno chcesz zatwierdzić to zamówienie?');">Zatwierdź</a>
                                <?php endif; ?>
                                
                                <!-- Przycisk do playlisty, jeśli link do playlisty istnieje -->
                                <?php if ($order['playlist_link']): ?>
                                    <a href="<?= htmlspecialchars($order['playlist_link']) ?>" target="_blank" class="button">Zobacz playlistę</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="admin_make_reservation.php" class="button">Dodaj rezerwację</a>
        <a href="admin_panel.php" class="button">Powrót do panelu administratora</a>
    </div>
</body>
</html>
