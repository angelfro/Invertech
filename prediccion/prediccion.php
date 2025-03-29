<?php
$archivo = "predicciones.txt";

if (!file_exists($archivo)) {
    echo "<h2>No hay predicciones disponibles.</h2>";
    exit;
}

$contenido = file($archivo);
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Predicciones</title>";
echo "<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        h2 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 80%;
            margin-top: 20px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>";
echo "</head>";
echo "<body>";
echo "<h2>Predicciones para los próximos 5 días</h2>";
echo "<table>";

// Mostrar encabezados
$encabezados = explode(",", trim($contenido[0]));
echo "<tr>";
foreach ($encabezados as $index => $encabezado) {
    if (strtolower($encabezado) !== "primera temperatura") { // Ocultar "primera temperatura"
        echo "<th>$encabezado</th>";
    }
}
echo "</tr>";

// Mostrar datos
for ($i = 1; $i < count($contenido); $i++) {
    $datos = explode(",", trim($contenido[$i]));
    echo "<tr>";
    foreach ($datos as $index => $dato) {
        if ($index !== array_search("primera temperatura", array_map('strtolower', $encabezados))) {
            echo "<td>$dato</td>";
        }
    }
    echo "</tr>";
}

echo "</table>";
echo "</body>";
echo "</html>";
?>
