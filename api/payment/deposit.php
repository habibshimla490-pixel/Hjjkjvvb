<?php
/**
 * Deposit Initiation Endpoint
 * User initiates a deposit request
 * Endpoint: POST /api/payment/deposit.php
 * 
 * Request:
 * {
 *   "user_id": 123,
 *   "amount": 500,
 *   "payment_method": "bkash"
 * }
 */

header('Content-Type: application/json');

try {
    // Load configuration
    $config = require __DIR__ . '/../../config/payment-gateway.php';
    
    // Load classes
    require __DIR__ . '/../../src/Database/Connection.php';
    require __DIR__ . '/../../src/Logger/PaymentLogger.php';
    require __DIR__ . '/../../src/Payment/DepositHandler.php';

    use App\Database\Connection;
    use App\Logger\PaymentLogger;
    use App\Payment\DepositHandler;

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

    if (!$userId || !$amount || !$paymentMethod) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Initialize logger
    $logger = new PaymentLogger(
        $config['winypay']['log_path'],
        $config['winypay']['log_level']
    );

    // Process deposit
    $depositHandler = new DepositHandler($config, $logger);
    $result = $depositHandler->initiateDeposit($userId, $amount, $paymentMethod);

    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'order_id' => $result['order_id'],
            'internal_txn_id' => $result['internal_txn_id'],
            'pay_url' => $result['pay_url'],
            'message' => 'Redirect user to pay_url to complete payment'
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
        $logger->logError('Deposit endpoint error', $e->getMessage());
    }
}
