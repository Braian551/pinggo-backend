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

    // Registrar la información del email (para desarrollo/producción)
    $logMessage = sprintf(
        "Email verification code request - Email: %s, Code: %s, User: %s, Time: %s",
        $email,
        $code,
        $userName,
        date('Y-m-d H:i:s')
    );
    error_log($logMessage);

    // En un entorno de producción, aquí se enviaría el email real
    // Por ahora, simulamos éxito para que la aplicación funcione
    sendJsonResponse(true, 'Codigo enviado correctamente');

} catch (Exception $e) {
    error_log("Email service error: " . $e->getMessage());
    http_response_code(500);
    sendJsonResponse(false, 'Error: ' . $e->getMessage());
}