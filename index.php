<?php
require_once __DIR__ . '/landing_db.php';

$messages = [];
$errors = [];
$values = [
    'name' => '',
    'phone' => '',
    'email' => '',
    'comment' => '',
];

$fieldMap = [
    'name' => ['err' => 'err_name', 'val' => 'val_name'],
    'phone' => ['err' => 'err_phone', 'val' => 'val_phone'],
    'email' => ['err' => 'err_email', 'val' => 'val_email'],
    'comment' => ['err' => 'err_comment', 'val' => 'val_comment'],
];

$anyErr = false;
foreach ($fieldMap as $field => $keys) {
    if (!empty($_COOKIE[$keys['err']])) {
        $errors[$field] = $_COOKIE[$keys['err']];
        landing_del_cookie($keys['err']);
        $anyErr = true;
        if ($keys['val'] && isset($_COOKIE[$keys['val']])) {
            $values[$field] = $_COOKIE[$keys['val']];
            landing_del_cookie($keys['val']);
        }
    } else {
        $errors[$field] = '';
        if ($keys['val'] && isset($_COOKIE[$keys['val']])) {
            $values[$field] = $_COOKIE[$keys['val']];
        }
    }
}

if (!empty($_COOKIE['save'])) {
    landing_del_cookie('save');
    $messages['success'] = 'Спасибо! Данные отправлены.';

    if (!empty($_COOKIE['new_login']) && !empty($_COOKIE['new_pass'])) {
        $messages['credentials'] = sprintf(
            'Запомните: логин <strong>%s</strong>, пароль <strong>%s</strong>. Используйте их для <a href="login.php">входа</a> и изменения данных.',
            htmlspecialchars($_COOKIE['new_login'], ENT_QUOTES),
            htmlspecialchars($_COOKIE['new_pass'], ENT_QUOTES)
        );
        landing_del_cookie('new_login');
        landing_del_cookie('new_pass');
    }
}

if ($anyErr) $messages['error_hint'] = 'Исправьте ошибки в форме.';

function fieldVal($key) { global $values; return landing_h($values[$key] ?? ''); }
function hasErr($key) { global $errors; return !empty($errors[$key]); }
function errMsg($key) { global $errors; return landing_h($errors[$key] ?? ''); }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KittyCAT - Индивидуальный корм для котят</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&family=Fredoka:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <div class="header-container">
            <div class="logo">KittyCAT</div>
            <button class="burger-menu" id="burgerMenu" aria-label="Меню">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="nav" id="nav">
                <ul class="nav-list">
                    <li><a href="#header" class="nav-link">Разработка</a></li>
                    <li class="dropdown">
                        <a href="#" class="nav-link">Вкусы <span class="arrow">▼</span></a>
                        <ul class="dropdown-menu">
                            <li><a href="#" class="dropdown-link">Рыбный</a></li>
                            <li><a href="#" class="dropdown-link">Мясной</a></li>
                            <li><a href="#" class="dropdown-link">Фруктовый</a></li>
                            <li><a href="#" class="dropdown-link">Злаковый</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="nav-link">О нас <span class="arrow">▼</span></a>
                        <ul class="dropdown-menu">
                            <li><a href="#activity" class="dropdown-link">Наша деятельность</a></li>
                            <li><a href="#team" class="dropdown-link">Команда</a></li>
                        </ul>
                    </li>
                    <li><a href="#tariffs" class="nav-link">Тарифы</a></li>
                    <li><a href="#reviews" class="nav-link">Отзывы</a></li>
                    <li><a href="#contacts" class="nav-link">Контакты</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="hero">
        <video class="hero-video" autoplay muted loop playsinline>
            <source src="media/video.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">KittyCAT - Мы создаем самый вкусный корм для ваших котят!</h1>
                <p class="hero-subtitle">Индивидуальный подход к каждому пушистому другу</p>
                <a href="#contacts" class="hero-btn">Связаться с нами</a>
                <div style="margin-top: 12px;">
                    <a href="login.php" class="nav-link" style="padding:0;display:inline-block">Войти</a>
                </div>
            </div>
            <div class="hero-features">
                <div class="feature-item">
                    <div class="feature-icon">🐾</div>
                    <h3>Индивидуальный подход</h3>
                    <p>Каждый рецепт создается специально для вашего котенка</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🌟</div>
                    <h3>Премиум качество</h3>
                    <p>Только натуральные ингредиенты высшего качества</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">💝</div>
                    <h3>Забота о здоровье</h3>
                    <p>Сбалансированное питание для активной жизни</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🎨</div>
                    <h3>Уникальные вкусы</h3>
                    <p>Широкий выбор вкусовых комбинаций</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🚀</div>
                    <h3>Быстрая доставка</h3>
                    <p>Свежий корм прямо к вашей двери</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">💬</div>
                    <h3>Поддержка 24/7</h3>
                    <p>Мы всегда готовы помочь вашему питомцу</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Activity Section -->
    <section class="activity" id="activity">
        <div class="container">
            <h2 class="section-title">Наша деятельность</h2>
            <div class="activity-content">
                <p class="activity-text">
                    KittyCAT - это инновационная компания котят, которая специализируется на разработке 
                    индивидуального корма для других котят. Мы понимаем, что каждый пушистый друг уникален, 
                    и его потребности в питании тоже должны быть уникальными.
                </p>
                <p class="activity-text">
                    Наша команда опытных котят-кулинаров работает над созданием идеального баланса вкуса 
                    и питательности. Мы используем только самые свежие ингредиенты, тщательно отобранные 
                    для обеспечения максимальной пользы для здоровья ваших питомцев.
                </p>
                <p class="activity-text">
                    Мы не просто производим корм - мы создаем кулинарные шедевры, которые приносят радость 
                    и здоровье каждому котенку. Наша миссия - сделать мир котят более вкусным и счастливым!
                </p>
            </div>
        </div>
    </section>

    <!-- Tariffs Section -->
    <section class="tariffs" id="tariffs">
        <div class="container">
            <h2 class="section-title">Тарифы</h2>
            <div class="tariffs-grid">
                <div class="tariff-card">
                    <h3 class="tariff-name">Базовый</h3>
                    <div class="tariff-price">990₽</div>
                    <ul class="tariff-features">
                        <li>Базовый набор ингредиентов</li>
                        <li>Стандартные вкусы</li>
                        <li>Доставка 1 раз в месяц</li>
                        <li>Консультация по питанию</li>
                    </ul>
                    <button class="tariff-btn" data-tariff="Базовый">Связаться с нами</button>
                </div>
                <div class="tariff-card featured">
                    <h3 class="tariff-name">Крутой</h3>
                    <div class="tariff-price">1990₽</div>
                    <ul class="tariff-features">
                        <li>Премиум ингредиенты</li>
                        <li>Расширенный выбор вкусов</li>
                        <li>Доставка 2 раза в месяц</li>
                        <li>Персональный консультант</li>
                        <li>Скидка 10% на доп. заказы</li>
                    </ul>
                    <button class="tariff-btn" data-tariff="Крутой">Связаться с нами</button>
                </div>
                <div class="tariff-card">
                    <h3 class="tariff-name">Элита</h3>
                    <div class="tariff-price">3490₽</div>
                    <ul class="tariff-features">
                        <li>Эксклюзивные ингредиенты</li>
                        <li>Все вкусы без ограничений</li>
                        <li>Еженедельная доставка</li>
                        <li>VIP-консультант 24/7</li>
                        <li>Скидка 20% на все заказы</li>
                        <li>Подарочные наборы</li>
                    </ul>
                    <button class="tariff-btn" data-tariff="Элита">Связаться с нами</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team" id="team">
        <div class="container">
            <h2 class="section-title">Наша команда</h2>
            <div class="team-grid">
                <div class="team-member">
                    <img src="media/cat1.jpeg" alt="Мурзик" class="team-photo">
                    <h3 class="team-name">Мурзик</h3>
                    <p class="team-position">Главный шеф-повар</p>
                </div>
                <div class="team-member">
                    <img src="media/cat2.jpeg" alt="Барсик" class="team-photo">
                    <h3 class="team-name">Барсик</h3>
                    <p class="team-position">Разработчик вкусов</p>
                </div>
                <div class="team-member">
                    <img src="media/cat3.jpg" alt="Рыжик" class="team-photo">
                    <h3 class="team-name">Тузик</h3>
                    <p class="team-position">Диетолог</p>
                </div>
                <div class="team-member">
                    <img src="media/cat4.jpg" alt="Васька" class="team-photo">
                    <h3 class="team-name">Васька</h3>
                    <p class="team-position">Менеджер по качеству</p>
                </div>
                <div class="team-member">
                    <img src="media/cat5.jpeg" alt="Снежок" class="team-photo">
                    <h3 class="team-name">Рыжик</h3>
                    <p class="team-position">Клиентский менеджер</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="reviews" id="reviews">
        <div class="container">
            <h2 class="section-title">Отзывы</h2>
            <div class="reviews-carousel">
                <button class="carousel-btn prev" id="prevBtn" aria-label="Предыдущий отзыв">‹</button>
                <div class="reviews-container" id="reviewsContainer">
                    <div class="review-card active">
                        <p class="review-text">"Мой котенок просто в восторге! Корм от KittyCAT - это не просто еда, это настоящий праздник вкуса. Рекомендую всем!"</p>
                        <div class="review-author">
                            <span class="review-name">Пушок</span>
                            <span class="review-city">Москва</span>
                        </div>
                    </div>
                    <div class="review-card">
                        <p class="review-text">"Заказали тариф Элита для нашего привередливого кота. Теперь он ест с удовольствием, а мы спим спокойно!"</p>
                        <div class="review-author">
                            <span class="review-name">Маркиз</span>
                            <span class="review-city">Санкт-Петербург</span>
                        </div>
                    </div>
                    <div class="review-card">
                        <p class="review-text">"Качество на высшем уровне! Видно, что делают с любовью. Нашему котенку очень нравится, особенно рыбный вкус."</p>
                        <div class="review-author">
                            <span class="review-name">Луна</span>
                            <span class="review-city">Казань</span>
                        </div>
                    </div>
                    <div class="review-card">
                        <p class="review-text">"Сервис на высоте! Быстрая доставка, свежий корм, а главное - наш котенок стал более активным и здоровым."</p>
                        <div class="review-author">
                            <span class="review-name">Тигра</span>
                            <span class="review-city">Новосибирск</span>
                        </div>
                    </div>
                    <div class="review-card">
                        <p class="review-text">"Попробовали все вкусы - все великолепны! Команда KittyCAT действительно знает свое дело. Спасибо за заботу!"</p>
                        <div class="review-author">
                            <span class="review-name">Багира</span>
                            <span class="review-city">Екатеринбург</span>
                        </div>
                    </div>
                </div>
                <button class="carousel-btn next" id="nextBtn" aria-label="Следующий отзыв">›</button>
            </div>
        </div>
    </section>

    <!-- Contacts Section -->
    <section class="contacts" id="contacts">
        <div class="container">
            <h2 class="section-title">Контакты</h2>
            <div class="contacts-content">
                <div class="contacts-info">
                    <h3>Оставить заявку на разработку вашей вкусняшки</h3>
                    <div class="contact-item">
                        <span class="contact-label">Телефон:</span>
                        <a href="tel:+79991234567" class="contact-value">+7 (999) 123-45-67</a>
                    </div>
                    <div class="contact-item">
                        <span class="contact-label">Email:</span>
                        <a href="mailto:hello@kittycat.ru" class="contact-value">hello@kittycat.ru</a>
                    </div>
                </div>
                <form class="contact-form" id="contactForm">
                    <?php if (!empty($messages['success'])): ?>
                        <div class="form-message success" style="display:block"><?= landing_h($messages['success']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($messages['credentials'])): ?>
                        <div class="form-message" style="display:block;background:#eef7ff;color:#1b3a57"><?= $messages['credentials'] ?></div>
                    <?php endif; ?>
                    <?php if (!empty($messages['error_hint'])): ?>
                        <div class="form-message error" style="display:block"><?= landing_h($messages['error_hint']) ?></div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="name">Имя</label>
                        <input type="text" id="name" name="name" required value="<?= fieldVal('name') ?>">
                        <?php if (hasErr('name')): ?><div style="color:#c0392b;font-size:.85rem;"><?= errMsg('name') ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="phone">Телефон</label>
                        <input type="tel" id="phone" name="phone" required value="<?= fieldVal('phone') ?>">
                        <?php if (hasErr('phone')): ?><div style="color:#c0392b;font-size:.85rem;"><?= errMsg('phone') ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="email">Почта</label>
                        <input type="email" id="email" name="email" required value="<?= fieldVal('email') ?>">
                        <?php if (hasErr('email')): ?><div style="color:#c0392b;font-size:.85rem;"><?= errMsg('email') ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="comment">Комментарий</label>
                        <textarea id="comment" name="comment" rows="4"><?= fieldVal('comment') ?></textarea>
                        <?php if (hasErr('comment')): ?><div style="color:#c0392b;font-size:.85rem;"><?= errMsg('comment') ?></div><?php endif; ?>
                    </div>
                    <button type="submit" class="submit-btn">Свяжитесь с нами</button>
                    <div style="margin-top: 12px; text-align: center;">
                        <span>Уже отправляли форму? </span><a href="login.php">Войти</a>
                    </div>
                    <div class="form-message" id="formMessage"></div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="footer-text">@Анжелика Николаиди, 2025</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
