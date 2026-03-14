<?php
session_start();
include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['id_user'])) {
    header('Location: login_register.php');
    exit();
}

$user_id = $_SESSION['id_user'];

// Pobieranie zamówień użytkownika
$stmt = $pdo->prepare("SELECT r.id_rezerwacji, r.termin, r.id_uslugi, o.nazwa AS usluga, r.status
                       FROM rezerwacja r
                       JOIN oferta_uslug o ON r.id_uslugi = o.id_uslugi
                       WHERE r.id_user = ?");
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie linku do playlisty dla każdej rezerwacji
foreach ($reservations as $key => $reservation) {
    $stmt = $pdo->prepare("SELECT link_playlista FROM playlisty WHERE id_rezerwacji = ?");
    $stmt->execute([$reservation['id_rezerwacji']]);
    $playlist = $stmt->fetch(PDO::FETCH_ASSOC);
    $reservations[$key]['playlist_link'] = $playlist ? $playlist['link_playlista'] : null; // Jeśli nie ma linku, ustawiamy null
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
    <div class="container">
        <h1>Twoje rezerwacje</h1>

        <?php if (empty($reservations)): ?>
            <p>Nie masz żadnych złożonych rezerwacji.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Usługa</th>
                        <th>Termin</th>
                        <th>Status</th>
                        <th>Link do playlisty</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?= htmlspecialchars($reservation['usluga']) ?></td>
                            <td><?= htmlspecialchars($reservation['termin']) ?></td>
                            <td>
                                <?php
                                // Wyświetlanie statusu
                                if ($reservation['status'] == 0) {
                                    echo "Oczekująca";
                                } else {
                                    echo "Zatwierdzona";
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($reservation['playlist_link']): ?>
                                    <a href="<?= htmlspecialchars($reservation['playlist_link']) ?>" target="_blank" class="button">Zobacz playlistę</a>
                                <?php else: ?>
                                    Brak linku do playlisty
                                <?php endif; ?>
                            </td>
                            <td>
                            <?php if ($reservation['status'] == 0): ?>
                                    <a href="edit_reservation.php?id=<?= $reservation['id_rezerwacji'] ?>" class="button">Edytuj</a>
                                    <a href="delete_reservation.php?id=<?= $reservation['id_rezerwacji'] ?>" class="button" onclick="return confirm('Czy na pewno chcesz usunąć to zamówienie?')">Usuń</a>
                                <?php else: ?>
                                    <button class="button" disabled>Edytuj</button>
                                    <button class="button" disabled>Usuń</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="user_dashboard.php" class="button">Powrót do panelu użytkownika</a>
    </div>
</body>
</html>
