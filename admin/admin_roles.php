<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login_register.php"); // Przekierowanie do strony logowania, jeśli nie jest zalogowany
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

// Pobranie wszystkich ról z bazy danych
$stmt_roles = $pdo->query("SELECT id_uprawnienia, nazwa_uprawnienia FROM uprawnienia");
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

// Obsługa dodawania nowej roli
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_role'])) {
    $new_role_name = trim($_POST['role_name']);

    if (empty($new_role_name)) {
        $error = "Nazwa roli nie może być pusta.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO uprawnienia (nazwa_uprawnienia) VALUES (?)");
            $stmt->execute([$new_role_name]);

            echo "<script>alert('Nowa rola została dodana.'); window.location.href = 'admin_roles.php';</script>";
        } catch (PDOException $e) {
            $error = "Błąd podczas dodawania roli: " . $e->getMessage();
        }
    }
}

// Obsługa usuwania roli
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_role'])) {
    $id_uprawnienia = intval($_POST['id_uprawnienia']);

    try {
        $stmt = $pdo->prepare("DELETE FROM uprawnienia WHERE id_uprawnienia = ?");
        $stmt->execute([$id_uprawnienia]);

        echo "<script>alert('Rola została usunięta.'); window.location.href = 'admin_roles.php';</script>";
    } catch (PDOException $e) {
        $error = "Błąd podczas usuwania roli: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzaj rolami</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Zarządzaj rolami</h1>

        <?php if (isset($error)): ?>
            <div class="error"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>

        <h2>Istniejące role</h2>
        <?php if (empty($roles)): ?>
            <p>Brak zdefiniowanych ról.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nazwa roli</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <td><?= htmlspecialchars($role['id_uprawnienia']) ?></td>
                            <td><?= htmlspecialchars($role['nazwa_uprawnienia']) ?></td>
                            <td>
                                <a href="admin_edit_role.php?id_uprawnienia=<?= $role['id_uprawnienia'] ?>" class="button">Edytuj</a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id_uprawnienia" value="<?= $role['id_uprawnienia'] ?>">
                                    <button type="submit" name="delete_role" class="button" onclick="return confirm('Czy na pewno chcesz usunąć tę rolę?');">Usuń</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>Dodaj nową rolę</h2>
        <form method="POST">
            <label for="role_name">Nazwa roli:</label>
            <input type="text" id="role_name" name="role_name" required>
            <button type="submit" name="new_role" class="button">Dodaj rolę</button>
        </form>

        <a href="admin_panel.php" class="button">Powrót do panelu administratora</a>
    </div>
</body>
</html>


