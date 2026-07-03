<?php
/**
 * Withdrawal Initiation Endpoint
 * User initiates a withdrawal request
 * Endpoint: POST /api/payment/withdraw.php
 * 
 * Request:
 * {
 *   "user_id": 123,
 *   "amount": 500,
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
    require __DIR__ . '/../../src/Logger/PaymentLogger.php';
    require __DIR__ . '/../../src/Payment/WithdrawHandler.php';

    use App\Database\Connection;
    use App\Logger\PaymentLogger;
    use App\Payment\WithdrawHandler;

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

    // Extract and validate inputs
    $userId = isset($input['user_id']) ? (int)$input['user_id'] : null;
    $amount = isset($input['amount']) ? (float)$input['amount'] : null;
    $paymentMethod = isset($input['payment_method']) ? (string)$input['payment_method'] : null;
    $accountName = isset($input['account_name']) ? (string)$input['account_name'] : null;
    $accountNumber = isset($input['account_number']) ? (string)$input['account_number'] : null;

    if (!$userId || !$amount || !$paymentMethod || !$accountName || !$accountNumber) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Initialize logger
    $logger = new PaymentLogger(
        $config['winypay']['log_path'],
        $config['winypay']['log_level']
    );

    // Process withdrawal
    $withdrawHandler = new WithdrawHandler($config, $logger);
    $result = $withdrawHandler->initiateWithdraw(
        $userId,
        $amount,
        $paymentMethod,
        $accountName,
        $accountNumber
    );

    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'order_id' => $result['order_id'],
            'internal_txn_id' => $result['internal_txn_id'],
            'message' => $result['message']
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'order_id' => $result['order_id'] ?? null,
            'message' => $result['message']
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);

    if (isset($logger)) {
        $logger->logError('Withdrawal endpoint error', $e->getMessage());
    }
}
