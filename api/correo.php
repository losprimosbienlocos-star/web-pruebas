<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function enviarCorreoHTML(
    $destino,
    $nombre,
    $asunto,
    $html
) {

    try {

        $mail = new PHPMailer(true);

        $mail->isSMTP();

        $mail->Host = getenv('SMTP_HOST');

        $mail->SMTPAuth = true;

        $mail->Username = getenv('SMTP_USER');

        $mail->Password = getenv('SMTP_PASS');

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port =
            getenv('SMTP_PORT') ?: 587;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom(
            getenv('SMTP_USER'),
            'CENGICAÑA'
        );

        $mail->addAddress(
            $destino,
            $nombre
        );

        $mail->isHTML(true);

        $mail->Subject = $asunto;

        $mail->Body = $html;

        return $mail->send();

    } catch (Exception $e) {

        error_log(
            'Error correo: '
            . $e->getMessage()
        );

        return false;
    }
}