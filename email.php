<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Certifique-se de que o autoload do Composer está configurado corretamente

function enviarEmail($para, $assunto, $mensagem) {
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.office365.com'; // Servidor SMTP do Outlook/Hotmail
        $mail->SMTPAuth = true;
        $mail->Username = 'kaiquemn10@hotmail.com'; // Utilize uma variável de ambiente para o e-mail
        $mail->Password = 'N10@fast'; // Utilize uma variável de ambiente para a senha
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS para segurança
        $mail->Port = 587;

        // Configuração do remetente e destinatário
        $mail->setFrom($mail->Username, 'Sistema CoreX'); // Usa o e-mail configurado como remetente
        $mail->addAddress($para); // Destinatário

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = $mensagem;

        // Envio do e-mail
        $mail->send();
        echo "E-mail enviado com sucesso.";
    } catch (Exception $e) {
        echo "Erro ao enviar e-mail: " . $e->getMessage();
    }
}
