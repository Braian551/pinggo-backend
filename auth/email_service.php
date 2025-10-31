<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

function getJsonInput() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'JSON invalido']);
        exit;
    }
    return $input;
}

function sendJsonResponse($success, $message, $data = []) {
    $response = ['success' => $success, 'message' => $message];
    if (!empty($data)) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

try {
    $input = getJsonInput();
    $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $code = $input['code'] ?? '';
    $userName = $input['userName'] ?? '';

    if (!$email || strlen($code) !== 6 || empty($userName)) {
        sendJsonResponse(false, 'Datos incompletos o invalidos');
    }

    // Usar la función mail() de PHP como alternativa más confiable
    $subject = 'Tu codigo de verificacion PingGo';
    $message = "Hola $userName,\n\nTu codigo de verificacion para PingGo es: $code\n\nEste codigo expirara en 10 minutos.\n\nSaludos,\nEl equipo de PingGo";

    $headers = [
        'From: PingGo <noreply@pinggo.com>',
        'Reply-To: support@pinggo.com',
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/plain; charset=UTF-8'
    ];

    if (mail($email, $subject, $message, implode("\r\n", $headers))) {
        sendJsonResponse(true, 'Codigo enviado correctamente');
    } else {
        throw new Exception("Error al enviar email usando mail()");
    }

} catch (Exception $e) {
    error_log("Email service error: " . $e->getMessage());
    http_response_code(500);
    sendJsonResponse(false, 'Error: ' . $e->getMessage());
}