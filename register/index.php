<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/../register.php';
    exit();
}

header('Location: ../index.php?auth=register');
exit();
