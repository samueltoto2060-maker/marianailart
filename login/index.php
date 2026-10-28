<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/../login.php';
    exit();
}

header('Location: ../index.php?auth=login');
exit();
