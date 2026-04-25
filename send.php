<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$to      = 'kgkarpittisztitas@gmail.com';
$name    = htmlspecialchars(trim($_POST['name'] ?? ''));
$phone   = htmlspecialchars(trim($_POST['phone'] ?? ''));
$email   = htmlspecialchars(trim($_POST['email'] ?? ''));
$service = htmlspecialchars(trim($_POST['szolgaltatas'] ?? ''));
$message = htmlspecialchars(trim($_POST['leiras'] ?? ''));

$subject = "Új ajánlatkérés: $name – $service";

$boundary = md5(time());

$headers  = "From: noreply@kgkarpit.hu\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

$body  = "--$boundary\r\n";
$body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
$body .= "
<html><body style='font-family:Arial,sans-serif;max-width:600px;'>
  <div style='background:#d4143e;padding:20px 28px;border-radius:8px 8px 0 0;'>
    <h2 style='color:#fff;margin:0;'>KG Kárpit – Új ajánlatkérés</h2>
  </div>
  <div style='padding:24px 28px;border:1px solid #e0e0e0;border-top:none;border-radius:0 0 8px 8px;'>
    <table style='width:100%;border-collapse:collapse;'>
      <tr><td style='padding:8px 0;border-bottom:1px solid #f0f0f0;color:#888;width:130px;'>👤 Név</td><td style='padding:8px 0;border-bottom:1px solid #f0f0f0;font-weight:bold;'>$name</td></tr>
      <tr><td style='padding:8px 0;border-bottom:1px solid #f0f0f0;color:#888;'>📞 Telefon</td><td style='padding:8px 0;border-bottom:1px solid #f0f0f0;font-weight:bold;color:#d4143e;'>$phone</td></tr>
      <tr><td style='padding:8px 0;border-bottom:1px solid #f0f0f0;color:#888;'>✉️ E-mail</td><td style='padding:8px 0;border-bottom:1px solid #f0f0f0;'>$email</td></tr>
      <tr><td style='padding:8px 0;border-bottom:1px solid #f0f0f0;color:#888;'>🛋️ Szolgáltatás</td><td style='padding:8px 0;border-bottom:1px solid #f0f0f0;'>$service</td></tr>
      <tr><td style='padding:8px 0;color:#888;vertical-align:top;'>💬 Leírás</td><td style='padding:8px 0;'>$message</td></tr>
    </table>
  </div>
</body></html>
\r\n";

// Több csatolmány kezelése
if (!empty($_FILES['attachment']['name'][0])) {
    foreach ($_FILES['attachment']['tmp_name'] as $i => $tmp) {
        if ($_FILES['attachment']['error'][$i] === 0) {
            $filename = basename($_FILES['attachment']['name'][$i]);
            $filetype = mime_content_type($tmp);
            $filedata = chunk_split(base64_encode(file_get_contents($tmp)));

            $body .= "--$boundary\r\n";
            $body .= "Content-Type: $filetype; name=\"$filename\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"$filename\"\r\n\r\n";
            $body .= "$filedata\r\n";
        }
    }
}

$body .= "--$boundary--";

$sent = mail($to, $subject, $body, $headers);

header('Content-Type: application/json');
echo json_encode(['success' => $sent]);
