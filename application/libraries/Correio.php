<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Correio
{
    public function enviar($destinatario, $assunto, $conteudo)
    {
        $autoload = FCPATH . 'vendor/autoload.php';
        if (is_file($autoload)) require_once $autoload;
        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            log_message('error', 'PHPMailer ainda não está instalado via Composer.');
            return FALSE;
        }
        $config = [];
        if (is_file(APPPATH . 'config/email.php')) require APPPATH . 'config/email.php';
        $remetente = getenv('VITAE_EMAIL_REMETENTE');
        if (!filter_var($remetente, FILTER_VALIDATE_EMAIL) || empty($config['smtp_host'])) return FALSE;
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(TRUE);
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->Port = (int) ($config['smtp_port'] ?? 587);
            $mail->SMTPAuth = !empty($config['smtp_user']);
            $mail->Username = $config['smtp_user'] ?? '';
            $mail->Password = $config['smtp_pass'] ?? '';
            $mail->SMTPSecure = $config['smtp_crypto'] ?? 'tls';
            $mail->Timeout = 15;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($remetente, 'Vitae RH');
            $mail->addAddress($destinatario);
            $mail->Subject = $assunto;
            $mail->Body = $conteudo;
            $mail->isHTML(FALSE);
            return $mail->send();
        } catch (\Exception $e) {
            log_message('error', 'Falha no transporte de e-mail. Verifique a configuração SMTP.');
            return FALSE;
        }
    }
}
