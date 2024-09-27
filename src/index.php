<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../vendor/autoload.php'; // Laad PHPMailer

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Logbestand configureren
$log = new Logger('klachtverwerking');
$log->pushHandler(new StreamHandler(__DIR__ . '/info.log', Logger::INFO));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $naam = $_POST['naam'];
    $email = $_POST['email'];
    $klacht = $_POST['klacht'];

    // Log de gegevens
    $log->info('Nieuwe klacht ontvangen', ['naam' => $naam, 'email' => $email, 'klacht' => $klacht]);

    // E-mail configureren
    $mail = new PHPMailer(true);
    
    try {
        // Server-instellingen
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];  // Vervang door jouw SMTP-server
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];  // Vervang door jouw SMTP-wachtwoord
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

        // Ontvanger en CC
        $mail->setFrom($_ENV['SMTP_USER'], 'Klachtverwerking');
        $mail->addAddress($email);  // Verzend naar de gebruiker
        $mail->addCC($_ENV['SMTP_USER']);  // Voeg jezelf toe als CC

        // Inhoud van de e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Uw klacht is in behandeling';
        $mail->Body    = "Beste $naam,<br><br>Uw klacht is in behandeling.<br><br><b>Uw gegevens:</b><br>Naam: $naam<br>Email: $email<br>Klacht: $klacht<br><br>Met vriendelijke groet,<br>Klachtverwerkingsteam";

        $mail->send();
        echo 'E-mail succesvol verzonden.';
    } catch (Exception $e) {
        // Log eventuele e-mail fouten
        $log->error('E-mail fout', ['error' => $mail->ErrorInfo]);
        echo "Er is een fout opgetreden bij het verzenden van de e-mail. Mailer Error: {$mail->ErrorInfo}";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klachtenformulier</title>
</head>
<body>
    <h2>Klachtenformulier</h2>
    <form method="post" action="">
        <label for="naam">Naam:</label><br>
        <input type="text" id="naam" name="naam" required><br><br>

        <label for="email">E-mail:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="klacht">Omschrijving van de klacht:</label><br>
        <textarea id="klacht" name="klacht" rows="4" cols="50" required></textarea><br><br>

        <input type="submit" value="Verstuur">
    </form>
</body>
</html>