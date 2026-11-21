<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 2; // Exibe a comunicação completa no navegador

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';                  
    $mail->SMTPAuth   = true;
    $mail->Username   = 'adotecczamericana@gmail.com';              
    $mail->Password   = 'sqeshuqmekotpjax';                  
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
    $mail->Port       = 587;                                    
    $mail->Timeout    = 15;

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        )
    );

    $mail->setFrom('adotecczamericana@gmail.com', 'Adote CCZ');       
    $mail->addAddress('adotecczamericana@gmail.com', 'Teste');       

    $mail->Subject = 'Teste SMTP Gmail';
    $mail->Body    = 'Teste de conexao enviado com sucesso!';

    $mail->send();
    echo "<br><b>E-mail enviado com sucesso!</b>";
} catch (Exception $e) {
    echo "<br><b>Erro capturado:</b> {$mail->ErrorInfo}";
}
?>