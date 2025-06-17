<?php
session_start(); // Запускаем сессию

// Функция для преобразования текста с URL в кликабельные ссылки
function makeClickableLinks($text) {
    $text = preg_replace(
        '/(https?:\/\/[^\s]+)/',
        '<a href="$1" target="_blank">$1</a>',
        $text
    );
    return $text;
}

// Задайте массив логинов и паролей
$validUsers = [
    ['username' => 'admin', 'password' => 'password1'],
    ['username' => 'editor', 'password' => 'password2'],
    ['username' => 'author', 'password' => 'password3'],
];

$isAuthenticated = isset($_SESSION['isAuthenticated']) ? $_SESSION['isAuthenticated'] : false;
$currentUser = isset($_SESSION['currentUser']) ? $_SESSION['currentUser'] : '';

// Проверяем, была ли отправлена форма для аутентификации
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    // Проверка логина и пароля
    foreach ($validUsers as $user) {
        if ($username === $user['username'] && $password === $user['password']) {
            $_SESSION['isAuthenticated'] = true; // Устанавливаем сессию
            $_SESSION['currentUser'] = $username; // Сохраняем текущего пользователя
            $isAuthenticated = true;
            $currentUser = $username;
            break;
        }
    }

    if (!$isAuthenticated) {
        echo "<p style='color:red;'>Неверный логин или пароль.</p>";
    }
}

// Проверяем, была ли отправлена форма для добавления поста
if ($isAuthenticated && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $title = htmlspecialchars($_POST['title']);
    $content = nl2br(htmlspecialchars($_POST['content'])); // Преобразуем переносы строк
    $content = makeClickableLinks($content); // Преобразуем URL в кликабельные ссылки

    // Добавляем логин автора к заголовку поста
    $postTitle = "$title (Автор: $currentUser)";

    // Обработка загрузки изображения (необязательная)
    $uploadDir = 'uploads/'; // Директория для загрузки изображений
    $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $uploadOk = 1;

    // Проверка на существование файла, если файл загружен
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Генерируем случайное имя файла
        $randomFileName = uniqid() . '.' . $imageFileType;
        $uploadFile = $uploadDir . $randomFileName;

        // Проверка типа файла
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Извините, только JPG, JPEG, PNG и GIF файлы разрешены.";
            $uploadOk = 0;
        }

        // Проверка на ошибки загрузки
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                // Сохраняем пост в файл с изображением
                $post = "<div class='p'><h2 class='pt'>$postTitle</h2><img src='$uploadFile' alt='$postTitle' style='max-width: 100%;' class='centered-image'><p class='pg'>$content</p></div><hr>";
                file_put_contents('posts.html', $post, FILE_APPEND);
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                echo "Извините, произошла ошибка при загрузке вашего файла.";
            }
        }
    } else {
        // Сохраняем пост в файл без изображения
        $post = "<div class='p'><h2 class='pt'>$postTitle</h2><p class='pg'>$content</p></div><hr>";
        file_put_contents('posts.html', $post, FILE_APPEND);
                header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Проверяем, была ли отправлена форма для удаления поста
if ($isAuthenticated && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $titleToDelete = htmlspecialchars($_POST['titleToDelete']);

    // Загружаем существующие посты
    $posts = file_get_contents('posts.html');

    // Разбиваем посты на массив
    $postsArray = explode("<hr>", $posts);
    $newPostsArray = [];

    // Удаляем пост с указанным заголовком, если это автор поста или администратор
    foreach ($postsArray as $post) {
        // Проверяем, является ли текущий пользователь автором поста или администратором
        if (strpos($post, "<h2 class='pt'>$titleToDelete (Автор: $currentUser)</h2>") === false && $currentUser !== 'admin') {
            $newPostsArray[] = $post; // Сохраняем пост, если это не тот, который нужно удалить
        }
    }

    // Сохраняем обновленный список постов
    file_put_contents('posts.html', implode("<hr>", $newPostsArray));
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Загружаем существующие посты
$posts = file_get_contents('posts.html');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporary Safe House</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class='page'>
        <?php if (!$isAuthenticated): ?>
            <h2 class='cpt'>Вход</h2>
            <form class="cpt" method="post" action="">
                <input name="username" placeholder="Логин" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <input type="hidden" name="action" value="login">
                <button type="submit" class="cbt">Войти</button>
            </form>
        <?php else: ?>
            <h2 class='pt'>Существующие записи</h2> <hr>
            <div>
                <?php echo $posts; ?>
            </div>

            <h2 class='pt'>Добавить новую запись</h2>
            <form method="post" action="" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Название записи" required> <br>
                <textarea name="content" placeholder="Текст записи" required></textarea> <br> <br>
                <input type="file" name="image" accept="image/*"> 
                <input type="hidden" name="action" value="add">
                <button type="submit" class="bt">Опубликовать</button>
            </form>
            
            <h2 class='pt'>Удалить запись</h2>
            <form method="post" action="">
                <input type="text" name="titleToDelete" placeholder="Название записи для удаления" required>
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="bt">Удалить</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

