<?php
// Conexión a la base de datos
$host = "localhost";
$user = "root"; // Cambia por tu usuario de la base de datos
$password = "Mipci2025@"; // Cambia por tu contraseña
$dbname = "clima_iot"; // Cambia por el nombre de tu base de datos

$conn = new mysqli($host, $user, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta para obtener los datos
$sql = "SELECT fecha_registro, temperatura_suelo, humedad_suelo FROM sensores LIMIT 4"; // Cambia por tu tabla y columnas
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>
