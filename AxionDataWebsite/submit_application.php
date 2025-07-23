<?php
// Telegram bot settings
$botToken = 'YOUR_TELEGRAM_BOT_TOKEN';
$chatId = 'YOUR_TELEGRAM_CHAT_ID';

// Sanitize and get POST data
$name = filter_var($_POST['fullname'], FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
$experience = filter_var($_POST['experience'], FILTER_SANITIZE_STRING);
$messageText = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

// Upload directory
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Function to upload file and return path
function uploadFile($fileKey, $uploadDir) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES[$fileKey]['tmp_name'];
        $fileName = basename($_FILES[$fileKey]['name']);
        $targetFilePath = $uploadDir . time() . '_' . $fileKey . '_' . $fileName;
        if (move_uploaded_file($tmpName, $targetFilePath)) {
            return $targetFilePath;
        }
    }
    return '';
}

// Upload DL images
$dlFrontPath = uploadFile('dlFront', $uploadDir);
$dlBackPath = uploadFile('dlBack', $uploadDir);

// Prepare message text
$text = "New Job Application:\n";
$text .= "Name: $name\nEmail: $email\nPhone: $phone\nExperience: $experience\nMessage: $messageText\n";

// Telegram API send photo URL
$sendPhotoUrl = "https://api.telegram.org/bot$botToken/sendPhoto";

// Initialize curl
$ch = curl_init();

// Send front DL image with caption
if ($dlFrontPath) {
    $post_fields = [
        'chat_id' => $chatId,
        'caption' => $text,
        'photo' => new CURLFile(realpath($dlFrontPath))
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
    curl_setopt($ch, CURLOPT_URL, $sendPhotoUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

    $output = curl_exec($ch);
}

// Send back DL image without caption
if ($dlBackPath) {
    $post_fields = [
        'chat_id' => $chatId,
        'photo' => new CURLFile(realpath($dlBackPath))
    ];

    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

    $output2 = curl_exec($ch);
}

// Close curl
curl_close($ch);

// Redirect to thank-you page
header('Location: thank-you.html');
exit;
?>
