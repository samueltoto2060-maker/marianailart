<?php
// Configuración de conexión a MySQL para XAMPP
$host = "localhost";
$user = "root";
$password = "";
$database = "maria_nail_art";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // Si la base de datos no existe aún o falla la conexión
    $conn = @new mysqli($host, $user, $password, $database);
    if ($conn->connect_error) {
        die("Error de conexión a MySQL. Asegúrate de iniciar Apache y MySQL en XAMPP. Error: " . $conn->connect_error);
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
