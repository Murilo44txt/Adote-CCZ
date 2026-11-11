<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';

function enviarEmailVerificacao($destinatario, $nome, $codigo) {
    $mail = new PHPMailer(true);

    try {
        // Configurações do Servidor SMTP (Outlook ou Gmail)
        $mail->isSMTP();
        $mail->Host       = 'smtp.office365.com'; // Ou 'smtp.gmail.com'
        $mail->SMTPAuth   = true;
        $mail->Username   = 'adoteccz@outlook.com';
        $mail->Password   = 'repzgdlnhurnfmve';
        
        // Configuração de segurança e portas
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 465;
        
        // Define um limite de tempo de 10 segundos para evitar o carregamento infinito
        $mail->Timeout    = 15; 

        // Correção de SSL para ambiente local (XAMPP)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom('adoteccz@outlook.com', 'Email Adote CCZ Americana');
        $mail->addAddress($destinatario, $nome);

        $mail->isHTML(true);
        $mail->Subject = 'Adote CCZ - Codigo de Validacao';
        $mail->Body    = "Olá <b>$nome</b>,<br><br>O seu código de verificação é: <h2>$codigo</h2><br>Este código expira em 15 minutos.";
        $mail->AltBody = "Olá $nome, O seu código de verificação é: $codigo. Este código expira em 15 minutos.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Em vez de congelar, interrompe e mostra o erro técnico exato
        echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
        return false;
    }
}
?>