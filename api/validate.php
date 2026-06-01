<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['valid' => false, 'msg' => 'Metodo nao permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$code      = strtoupper(trim($input['code']      ?? ''));
$device_id = trim($input['device_id'] ?? '');

if (!$code || !$device_id) {
    echo json_encode(['valid' => false, 'msg' => 'Dados incompletos']);
    exit;
}

$file = __DIR__ . '/data/codes.json';
if (!file_exists($file)) {
    echo json_encode(['valid' => false, 'msg' => 'Sistema indisponivel']);
    exit;
}

$fp = fopen($file, 'r+');
if (!flock($fp, LOCK_EX)) {
    echo json_encode(['valid' => false, 'msg' => 'Tente novamente']);
    exit;
}

$codes = json_decode(file_get_contents($file), true) ?: [];

if (!isset($codes[$code])) {
    flock($fp, LOCK_UN);
    fclose($fp);
    echo json_encode(['valid' => false, 'msg' => 'Codigo invalido. Verifique e tente novamente.']);
    exit;
}

$entry = &$codes[$code];

// Codigo ja vinculado a outro dispositivo
if (!empty($entry['device_id']) && $entry['device_id'] !== $device_id) {
    flock($fp, LOCK_UN);
    fclose($fp);
    echo json_encode(['valid' => false, 'msg' => 'Este codigo ja esta em uso em outro dispositivo. Entre em contato: contato@getfinly.com.br']);
    exit;
}

// Vincula ao dispositivo se for o primeiro uso
if (empty($entry['device_id'])) {
    $entry['device_id']  = $device_id;
    $entry['first_used'] = date('Y-m-d H:i:s');
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($codes, JSON_PRETTY_PRINT));
}

flock($fp, LOCK_UN);
fclose($fp);

echo json_encode(['valid' => true, 'plan' => $entry['plan']]);
