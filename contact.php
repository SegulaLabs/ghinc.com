<?php
// Allowed origins (GitHub Pages + custom domain)
$allowed = [
    'https://segulalabs.github.io',
    'https://ghinc.com',
    'https://www.ghinc.com',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=UTF-8');
header('Vary: Origin');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

// Honeypot — bots fill this, humans don't
if (!empty($_POST['website'])) {
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Sanitize
$name    = trim(strip_tags($_POST['name']    ?? ''));
$email   = trim(strip_tags($_POST['email']   ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

// Validate
if (empty($name)) {
    http_response_code(422);
    echo json_encode(['error' => 'Name is required.']);
    exit;
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['error' => 'A valid email address is required.']);
    exit;
}
if (empty($message)) {
    http_response_code(422);
    echo json_encode(['error' => 'Message is required.']);
    exit;
}

// Compose email
$to      = 'ari@ghinc.com';
$subject = 'New message from GHI contact form';
$body    = "Name:    $name\nEmail:   $email\n\nMessage:\n$message";
$headers = implode("\r\n", [
    'From: noreply@ghinc.com',
    "Reply-To: $name <$email>",
    'X-Mailer: PHP/' . PHP_VERSION,
    'Content-Type: text/plain; charset=UTF-8',
]);

if (mail($to, $subject, $body, $headers)) {
    http_response_code(200);
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send. Please email us directly at ari@ghinc.com.']);
}
