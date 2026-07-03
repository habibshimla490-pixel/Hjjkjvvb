<?php
/**
 * Withdrawal Callback Handler
 * Receives and processes withdrawal callbacks from WinyPay
 * Endpoint: https://yourdomain.com/api/payment/withdraw-callback.php
 */

header('Content-Type: application/json');

try {
    // Load configuration
    $config = require __DIR__ . '/../../config/payment-gateway.php';
    
    // Load classes
    require __DIR__ . '/../../src/Database/Connection.php';
    require __DIR__ . '/../../src/Logger/PaymentLogger.php';
    require __DIR__ . '/../../src/Security/CallbackValidator.php';
    require __DIR__ . '/../../src/Payment/WithdrawHandler.php';

    use App\Database\Connection;
    use App\Logger\PaymentLogger;
    use App\Security\CallbackValidator;
    use App\Payment\WithdrawHandler;

    // Get raw request body for signature verification
    $rawBody = file_get_contents('php://input');
    $callbackData = json_decode($rawBody, true);

    if (!$callbackData) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
        exit;
    }

    // Initialize logger
    $logger = new PaymentLogger(
        $config['winypay']['log_path'],
        $config['winypay']['log_level']
    );

    // Log incoming callback
    $logger->logCallback('withdraw', $callbackData);

    // Verify signature
    $signature = $_SERVER['HTTP_X_CALLBACK_SIGN'] ?? null;
    
    if (!$signature) {
        $logger->logError('Missing callback signature header', 'X-Callback-Sign not provided');
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $validator = new CallbackValidator($config['winypay']['payout_key']);

    if (!$validator->validateSignature($rawBody, $signature)) {
        $logger->logError('Callback signature validation failed', 'Signature mismatch', [
            'provided' => $signature,
            'order_id' => $callbackData['order_id'] ?? null
        ]);
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Signature verification failed']);
        exit;
    }

    $logger->logCallback('withdraw', $callbackData, $signature, true);

    // Validate payload structure
    if (!$validator->validatePayloadStructure($callbackData, 'withdraw')) {
        $logger->logError('Invalid callback payload structure', 'Missing required fields', $callbackData);
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
        exit;
    }

    // Check for duplicate callback
    if ($validator->isDuplicateCallback($callbackData['transaction_id'])) {
        $logger->logCallbackProcessing(
            $callbackData['transaction_id'],
            'duplicate',
            'Callback already processed'
        );
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Callback already processed']);
        exit;
    }

    // Mark as processed to prevent duplicates
    $validator->markCallbackProcessed(
        $callbackData['transaction_id'],
        $callbackData['order_id'],
        $signature,
        $rawBody
    );

    // Validate user
    if (!$validator->validateUser($callbackData['user_id'])) {
        $logger->logError('Invalid user in callback', 'User validation failed', $callbackData);
        http_response_code(400);
        echo json_encode(['status' => 'success']); // Return success to prevent retry
        exit;
    }

    // Process callback
    $withdrawHandler = new WithdrawHandler($config, $logger);
    $result = $withdrawHandler->processWithdrawCallback($callbackData);

    $logger->logCallbackProcessing(
        $callbackData['transaction_id'],
        $callbackData['status'],
        $result['message']
    );

    http_response_code(200);
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    
    if (isset($logger)) {
        $logger->logError('Withdrawal callback processing error', $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
}
