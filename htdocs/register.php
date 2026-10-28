<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['auth_action'] = 'register';
    $_GET['auth'] = 'register';
    require __DIR__ . '/index.php';
    exit();
}

header('Location: index.php?auth=register');
exit();
