<?php
// obtener_datos_sensor.php

// Configuración de la base de datos
$host = "localhost";
$user = "root";           // Cambia por tu usuario
$password = "Mipci2025@";  // Cambia por tu contraseña
$dbname = "clima_iot";      // Cambia por el nombre de tu BD

$conn = new mysqli($host, $user, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta: Obtener los últimos 10 registros de 'temperatura_suelo'
// Ordenados de forma descendente para tomar los más recientes.
$sql = "SELECT temperatura_suelo FROM sensores ORDER BY fecha_registro DESC LIMIT 10";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Convertimos a número (float) si es necesario
        $data[] = floatval($row['temperatura_suelo']);
    }
}

// Invertimos el arreglo para que los datos queden en orden cronológico
$data = array_reverse($data);

header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>
