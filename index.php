<?php
session_start();
include 'config.php';

try {
    // Pobieranie danych z bazy
    $stmt_offers = $pdo->query("SELECT nazwa, cena, opis FROM oferta_uslug");
    $offers = $stmt_offers->fetchAll(PDO::FETCH_ASSOC);

    $stmt_promotions = $pdo->query("SELECT nazwa, opis, rabat FROM promocje WHERE NOW() BETWEEN data_rozpoczecia AND data_zakonczenia");
    $promotions = $stmt_promotions->fetchAll(PDO::FETCH_ASSOC);

    $stmt_reviews = $pdo->query("
        SELECT o.tresc, o.data_opinii, o.ocena, u.imie, u.nazwisko 
        FROM opinie_klientow o
        JOIN uzytkownicy u ON o.id_user = u.id_user
        ORDER BY o.data_opinii DESC
        LIMIT 5
    ");
    $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

    /* stara wersja z data_pytania*/
    $stmt_questions = $pdo->query("
        SELECT f.pytanie, f.odpowiedz, u.imie, u.nazwisko, f.data_pytania 
        FROM faq f
        JOIN uzytkownicy u ON f.id_user = u.id_user
        ORDER BY f.data_pytania DESC
        LIMIT 5
    ");
    

    // $stmt_questions = $pdo->query("
    //     SELECT f.pytanie, f.odpowiedz, u.imie, u.nazwisko 
    //     FROM faq f
    //     JOIN uzytkownicy u ON f.id_user = u.id_user
    //     LIMIT 5
    // ");
    $questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Błąd podczas pobierania danych: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wynajem DJ-a</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Witamy na stronie wynajmu DJ-a!</h1>
        <?php if (isset($_SESSION['id_user'])): ?>
            <?php if (!empty($_SESSION['admin'])): ?>
                <a href="admin_panel.php" class="button">Panel administratora</a>
            <?php elseif (!empty($_SESSION['id_uprawnienia']) && $_SESSION['id_uprawnienia'] == 2): ?>
                <a href="user_dashboard.php" class="button">Panel użytkownika</a>
            <?php else: ?>
                <a href="moderator_dashboard.php" class="button">Panel moderatora</a>
            <?php endif; ?>
            <a href="logout.php" class="button">Wyloguj się</a>
        <?php else: ?>
            <a href="login_register.php" class="button">Zaloguj się</a>
            <a href="rejestracja_register.php" class="button">Zarejestruj się</a>
        <?php endif; ?>
        
        <!-- Sekcja ofert -->
        <section>
            <h2>Nasze Usługi</h2>
            <div class="services">
                <?php foreach ($offers as $offer): ?>
                    <div class="services-item">
                        <h3><?= htmlspecialchars($offer['nazwa']) ?></h3>
                        <p><?= htmlspecialchars($offer['opis']) ?></p>
                        <span><strong>Cena:</strong> <?= number_format($offer['cena'], 2) ?> PLN</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Sekcja promocji -->
        <section>
            <h2>Promocje</h2>
            <div class="promotions">
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
            </div>
        </section>

        <!-- Sekcja opinii -->
        <section>
            <h2>Opinie Klientów</h2>
            <div class="reviews">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <blockquote class="reviews-item">
                            <p>"<?= htmlspecialchars($review['tresc']) ?>"</p>
                            <footer>
                                <strong><?= htmlspecialchars($review['imie'] . ' ' . $review['nazwisko']) ?></strong>, 
                                <?= htmlspecialchars($review['data_opinii']) ?> 
                                (Ocena: <?= htmlspecialchars($review['ocena']) ?>/5)
                            </footer>
                        </blockquote>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Brak opinii do wyświetlenia.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Sekcja pytań i odpowiedzi -->
        <section>
            <h2>Pytania i Odpowiedzi</h2>
            <div class="faq">
                <?php if (!empty($questions)): ?>
                    <?php foreach ($questions as $question): ?>
                        <div class="faq-item">
                            <blockquote>
                                <p><strong>Pytanie:</strong> <?= htmlspecialchars($question['pytanie']) ?></p>
                                <?php if (!empty($question['odpowiedz'])): ?>
                                    <p><strong>Odpowiedź:</strong> <?= htmlspecialchars($question['odpowiedz']) ?></p>
                                <?php else: ?>
                                    <p><em>Brak odpowiedzi</em></p>
                                <?php endif; ?>
                                <footer>
                                    <!-- <small>Dodane przez: <?= htmlspecialchars($question['imie'] . ' ' . $question['nazwisko']) ?>, <?= htmlspecialchars($question['data_pytania']) ?></small> -->
                                </footer>
                            </blockquote>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Brak pytań do wyświetlenia.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>
