<?php
/**
 * Bind Payment Account Endpoint
 * User binds their payment account (bKash, Nagad, Rocket, USDT)
 * Endpoint: POST /api/payment/bind-account.php
 * 
 * Request:
 * {
 *   "user_id": 123,
 *   "payment_method": "bkash",
 *   "account_name": "John Doe",
 *   "account_number": "01712345678"
 * }
 */

header('Content-Type: application/json');

try {
    // Load configuration
    $config = require __DIR__ . '/../../config/payment-gateway.php';
    
    // Load classes
    require __DIR__ . '/../../src/Database/Connection.php';

    use App\Database\Connection;

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    // Get JSON payload
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request body']);
        exit;
    }

    // Extract inputs
    $userId = isset($input['user_id']) ? (int)$input['user_id'] : null;
    $paymentMethod = isset($input['payment_method']) ? (string)$input['payment_method'] : null;
    $accountName = isset($input['account_name']) ? (string)$input['account_name'] : null;
    $accountNumber = isset($input['account_number']) ? (string)$input['account_number'] : null;

    if (!$userId || !$paymentMethod || !$accountName || !$accountNumber) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Validate payment method
    if (!array_key_exists($paymentMethod, $config['winypay']['payment_methods'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid payment method']);
        exit;
    }

    // Get database connection
    $db = Connection::getInstance()->getConnection();

    // Check if account already exists
    $checkQuery = "SELECT id FROM user_payment_accounts 
                   WHERE user_id = ? AND payment_method = ? AND account_number = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$userId, $paymentMethod, $accountNumber]);

    if ($checkStmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Account already bound']);
        exit;
    }

    // Insert new payment account
    $query = "INSERT INTO user_payment_accounts 
              (user_id, payment_method, account_name, account_number, is_primary, is_active) 
              VALUES (?, ?, ?, ?, 1, 1)";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId, $paymentMethod, $accountName, $accountNumber]);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Account bound successfully',
        'payment_method' => $paymentMethod,
        'account_name' => $accountName,
        'account_number' => $accountNumber,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
