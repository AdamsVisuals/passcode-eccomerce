<?php
header('Content-Type: application/json');

// Database configuration - UPDATE THESE WITH YOUR CREDENTIALS
$host = 'localhost';
$dbname = 'passcodeusers';
$username = 'root';  // Replace with your database username
$password = '';  // Replace with your database password

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate data
    if (empty($data['fullname']) || empty($data['email']) || empty($data['password'])) {
        throw new Exception('Full name, email, and password are required');
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email already registered');
    }

    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['fullname'],
        $data['email'],
        $hashedPassword,
        $data['phone'] ?? null
    ]);

    // Return success
    echo json_encode(['success' => true, 'message' => 'Account created successfully']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>