<?php
session_start();

// Function to send JSON response
function sendJsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Check if this is a Google login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data && isset($data['loginType']) && $data['loginType'] === 'google') {
        // Handle Google login
        $_SESSION['user'] = [
            'email' => $data['email'],
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'loginType' => 'google'
        ];
        sendJsonResponse(['success' => true]);
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Invalid data received']);
    }
}

// Check if this is a session check request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['user'])) {
        sendJsonResponse([
            'loggedIn' => true,
            'user' => $_SESSION['user']
        ]);
    } else {
        sendJsonResponse([
            'loggedIn' => false
        ]);
    }
}
?>