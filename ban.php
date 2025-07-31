<?php
// === CONFIG ===
$botToken = '7166403259:AAHcKMLdIWrWZdlf1jp7Vuubd3Tdczov7Eo';
$secret = ''; // اجعلها كلمة سر بسيطة مثلاً "12345" لحماية الوصول (اختياري)
$bannedFile = __DIR__ . '/banned_ips.txt';

// === READ CALLBACK ===
$content = file_get_contents("php://input");
$update = json_decode($content, true);

// === VALIDATE ===
if (!isset($update['callback_query']['data'])) {
    exit;
}

$data = $update['callback_query']['data'];
$callbackId = $update['callback_query']['id'];
$chatId = $update['callback_query']['message']['chat']['id'];
$user = $update['callback_query']['from']['first_name'] ?? '';

// === HANDLE BAN ===
if (strpos($data, 'ban:') === 0) {
    $ip = substr($data, 4);

    // سجل الـ IP في banned_ips.txt
    file_put_contents($bannedFile, "$ip\n", FILE_APPEND | LOCK_EX);

    // رد تأكيدي
    $replyUrl = "https://api.telegram.org/bot$botToken/answerCallbackQuery";
    $replyData = [
        'callback_query_id' => $callbackId,
        'text' => "✅ تم حظر IP: $ip بنجاح",
        'show_alert' => true
    ];
    file_get_contents($replyUrl . '?' . http_build_query($replyData));

    // أرسل إشعار إلى نفس الشات
    $notifyUrl = "https://api.telegram.org/bot$botToken/sendMessage";
    $notifyData = [
        'chat_id' => $chatId,
        'text' => "🚫 $user قام بحظر IP: $ip"
    ];
    file_get_contents($notifyUrl . '?' . http_build_query($notifyData));
}
