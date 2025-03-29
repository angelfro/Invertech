<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendAlertEmail($recipientEmails, $alertas) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Configuración del servidor SMTP de Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        
        // Configurar juego de caracteres
        $mail->CharSet = 'UTF-8';

        // Credenciales de Gmail (ajusta estos valores a los tuyos)
        $mail->Username = 'amartinezhernandez79@gmail.com';
        $mail->Password = 'zmio qrnh jhow sszd';

        // Configuración del correo electrónico
        $mail->setFrom('amartinezhernandez79@gmail.com', 'AFO SENTINEL');
        
        // Agregar múltiples destinatarios
        foreach ($recipientEmails as $recipient) {
            $mail->addAddress($recipient);
        }
        
        $mail->Subject = '⚠️ Alerta del Sistema IoT';
        $mail->isHTML(true);

        // Crear el contenido de las alertas en formato HTML
        $alertContent = "<ul style='padding: 0;'>";
        foreach ($alertas as $alerta) {
            $alertContent .= "<li style='background: #fce4ec; margin: 5px 0; padding: 10px; border-radius: 5px; color: #c2185b; list-style: none;'>$alerta</li>";
        }
        $alertContent .= "</ul>";

        // Construir el cuerpo del mensaje con un template bonito y meta charset para acentos
        $mail->Body = "
            <html>
              <head>
                <meta charset='UTF-8'>
                <style>
                  body { background-color: #f4f4f4; font-family: Arial, sans-serif; margin: 0; padding: 0; }
                  .container { background-color: #ffffff; padding: 20px; margin: 30px auto; width: 90%; max-width: 600px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                  h1 { color: #333333; }
                  p { color: #555555; }
                </style>
              </head>
              <body>
                <div class='container'>
                  <h1>Alerta del Sistema IoT</h1>
                  <p>Se han detectado las siguientes alertas en el sistema IoT:</p>
                  $alertContent
                  <p>Por favor, revise el sistema de inmediato.</p>
                </div>
              </body>
            </html>";

        // Enviar el correo
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando alerta: " . $mail->ErrorInfo);
        return false;
    }
}

// Procesar la solicitud POST y enviar las alertas a dos correos específicos
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $datos = json_decode(file_get_contents("php://input"), true);
    
    if (!empty($datos["alertas"])) {
        // Define los correos de destino
        $recipients = [
            "angel.afosentinel@gmail.com",
            "oscar.afosentinel@gmail.com"
        ];
        
        if (sendAlertEmail($recipients, $datos["alertas"])) {
            echo json_encode(["estado" => "success", "mensaje" => "Correo enviado correctamente"]);
        } else {
            echo json_encode(["estado" => "error", "mensaje" => "Error al enviar el correo"]);
        }
    } else {
        echo json_encode(["estado" => "info", "mensaje" => "No hay alertas para enviar"]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Método no permitido"]);
}
?>
