<?php
include 'config.php';

include 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany i ma uprawnienia administratora
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: login_register.php"); // Przekierowanie do strony logowania, jeśli nie jest zalogowany
    exit();
}

$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT id_uprawnienia FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jeśli użytkownik nie jest administratorem, przekierowanie
if (!$user || !in_array($user['id_uprawnienia'], [1])) {
    header("Location: user_dashboard.php");
    exit();
}

// Pobranie wszystkich ofert z bazy
$stmt = $pdo->query("SELECT id_uslugi, nazwa, opis, cena FROM oferta_uslug");
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzaj ofertą usług</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Zarządzaj ofertą usług</h1>

        <?php if (empty($offers)): ?>
            <p>Brak ofert do wyświetlenia.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nazwa</th>
                        <th>Opis</th>
                        <th>Cena</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($offers as $offer): ?>
                        <tr>
                            <td><?= htmlspecialchars($offer['nazwa']) ?></td>
                            <td><?= htmlspecialchars($offer['opis']) ?></td>
                            <td><?= number_format($offer['cena'], 2) ?> PLN</td>
                            <td>
                                <a href="admin_edit_offer.php?id_uslugi=<?= $offer['id_uslugi'] ?>" class="button">Edytuj</a>
                                <a href="admin_delete_offer.php?id_uslugi=<?= $offer['id_uslugi'] ?>" class="button" onclick="return confirm('Czy na pewno chcesz usunąć tę usługę?');">Usuń</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="admin_add_offer.php" class="button">Dodaj nową usługę</a>
        <a href="admin_panel.php" class="button">Powrót do panelu administratora</a>
    </div>
</body>
</html>
