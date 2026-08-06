<?php
// ============================================================
// gemini-handler.php — يستقبل رسالة المستخدم من app.js ويرسلها إلى Gemini
// ثم يعيد الرد كـ JSON. مفتاح الـ API لا يظهر أبدًا في المتصفح.
// ============================================================

header('Content-Type: application/json; charset=utf-8');

// اسمح فقط بطلبات POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['reply' => 'الطريقة غير مسموحة.']);
    exit;
}

require_once __DIR__ . '/config.php';

if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '' || GEMINI_API_KEY === 'ضع_مفتاحك_هنا') {
    http_response_code(500);
    echo json_encode(['reply' => 'لم يتم إعداد مفتاح Gemini API بعد. عدّل ملف config.php']);
    exit;
}

// اقرأ الطلب القادم من app.js: { "prompt": "..." }
$input = json_decode(file_get_contents('php://input'), true);
$prompt = isset($input['prompt']) ? trim($input['prompt']) : '';

if ($prompt === '') {
    http_response_code(400);
    echo json_encode(['reply' => 'لم يتم إرسال أي نص.']);
    exit;
}

// جهّز طلب Gemini API
$url = 'https://generativelanguage.googleapis.com/v1beta/models/'
     . GEMINI_MODEL . ':generateContent?key=' . GEMINI_API_KEY;

$payload = [
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['reply' => 'تعذر الاتصال بـ Gemini: ' . $curlError]);
    exit;
}

$data = json_decode($response, true);

if ($httpCode !== 200) {
    $msg = $data['error']['message'] ?? 'خطأ غير معروف من Gemini.';
    http_response_code($httpCode);
    echo json_encode(['reply' => 'خطأ من Gemini: ' . $msg]);
    exit;
}

// استخرج نص الرد من استجابة Gemini
$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$reply) {
    http_response_code(502);
    echo json_encode(['reply' => 'لم يصل رد واضح من Gemini.']);
    exit;
}

echo json_encode(['reply' => $reply], JSON_UNESCAPED_UNICODE);
