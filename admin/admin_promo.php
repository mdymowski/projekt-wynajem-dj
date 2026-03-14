<?php
include '../config.php';

// Sprawdzenie, czy użytkownik jest zalogowany i ma uprawnienia administratora
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: ../login_register.php"); // Przekierowanie do strony logowania, jeśli nie jest zalogowany
    exit();
}

$user_id = $_SESSION['id_user'];
$stmt = $pdo->prepare("SELECT id_uprawnienia FROM uzytkownicy WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jeśli użytkownik nie jest administratorem, przekierowanie
if (!$user || !in_array($user['id_uprawnienia'], [1])) {
    header("Location: ../user/user_dashboard.php");
    exit();
}

// Pobranie wszystkich promocji z bazy
$stmt = $pdo->query("SELECT id_promocji, nazwa, opis, rabat, data_rozpoczecia, data_zakonczenia FROM promocje");
$promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzaj promocjami</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Zarządzaj promocjami</h1>

        <?php if (empty($promotions)): ?>
            <p>Brak promocji do wyświetlenia.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nazwa</th>
                        <th>Opis</th>
                        <th>Rabat (%)</th>
                        <th>Data rozpoczęcia</th>
                        <th>Data zakończenia</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promotions as $promotion): ?>
                        <tr>
                            <td><?= htmlspecialchars($promotion['nazwa']) ?></td>
                            <td><?= htmlspecialchars($promotion['opis']) ?></td>
                            <td><?= htmlspecialchars($promotion['rabat']) ?>%</td>
                            <td><?= htmlspecialchars($promotion['data_rozpoczecia']) ?></td>
                            <td><?= htmlspecialchars($promotion['data_zakonczenia']) ?></td>
                            <td>
                                <a href="admin_edit_promo.php?id_promocji=<?= $promotion['id_promocji'] ?>" class="button">Edytuj</a>
                                <a href="admin_delete_promo.php?id_promocji=<?= $promotion['id_promocji'] ?>" class="button" onclick="return confirm('Czy na pewno chcesz usunąć tę promocję?');">Usuń</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="admin_add_promo.php" class="button">Dodaj nową promocję</a>
        <a href="admin_panel.php" class="button">Powrót do panelu administratora</a>
    </div>
</body>
</html>


