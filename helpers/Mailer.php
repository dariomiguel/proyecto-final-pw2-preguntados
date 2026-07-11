<?php
class Mailer {
    public static function enviarVerificacion($mail, $nombre, $token) {
        require_once __DIR__ . '/../vendor/autoload.php';

        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host       = 'smtp.gmail.com';
            $mailer->SMTPAuth   = true;
            $mailer->Username   = 'santiagochaumeri@gmail.com';
            $mailer->Password   = 'tqljfsfyzlsvmgvh';
            $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mailer->Port       = 587;
            $mailer->CharSet    = 'UTF-8';

            $mailer->setFrom('santiagochaumeri@gmail.com', 'Preguntados UNLaM');
            $mailer->addAddress($mail, $nombre);

            $link = 'http://localhost/registro/verificar?token=' . $token;

            $mailer->isHTML(true);
            $mailer->Subject = 'Verificá tu cuenta - Preguntados UNLaM';
            $mailer->Body    = "
                <div style='font-family:sans-serif;max-width:480px;margin:auto;'>
                    <h2>¡Bienvenido/a, {$nombre}!</h2>
                    <p>Hacé clic para verificar tu cuenta:</p>
                    <a href='{$link}'
                       style='background:#4d7cfe;color:#fff;padding:12px 24px;
                              text-decoration:none;border-radius:8px;display:inline-block;'>
                        Verificar cuenta
                    </a>
                    <p style='color:#888;font-size:.85em;margin-top:16px;'>
                        O copiá este link: {$link}
                    </p>
                </div>
            ";

            $mailer->send();
            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            Log::error("Mailer error: " . $mailer->ErrorInfo);
            return false;
        }
    }
}