<?php
/**
 * Get Wallet Details Endpoint
 * Retrieve user wallet balance and transaction history
 * Endpoint: GET /api/payment/wallet.php?user_id=123
 */

header('Content-Type: application/json');

try {
    // Load configuration
    $config = require __DIR__ . '/../../config/payment-gateway.php';
    
    // Load classes
    require __DIR__ . '/../../src/Database/Connection.php';

    use App\Database\Connection;

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    // Get user ID
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'user_id is required']);
        exit;
    }

    // Get database connection
    $db = Connection::getInstance()->getConnection();

    // Get wallet details
    $query = "SELECT * FROM user_wallets WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    $wallet = $stmt->fetch();

    if (!$wallet) {
        // Create wallet if doesn't exist
        $createQuery = "INSERT INTO user_wallets (user_id, balance, bonus) VALUES (?, 0, 0)";
        $createStmt = $db->prepare($createQuery);
        $createStmt->execute([$userId]);
        
        $wallet = [
            'user_id' => $userId,
            'balance' => 0,
            'bonus' => 0,
            'total_deposited' => 0,
            'total_withdrawn' => 0,
            'daily_withdraw_amount' => 0,
        ];
    }

    // Get recent transactions
    $transQuery = "SELECT * FROM payment_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
    $transStmt = $db->prepare($transQuery);
    $transStmt->execute([$userId]);
    $transactions = $transStmt->fetchAll();

    // Get withdrawal limit remaining
    $today = date('Y-m-d');
    $dailyLimitQuery = "SELECT daily_withdraw_amount FROM user_wallets 
                        WHERE user_id = ? AND daily_withdraw_date = ?";
    $dailyStmt = $db->prepare($dailyLimitQuery);
    $dailyStmt->execute([$userId, $today]);
    $dailyResult = $dailyStmt->fetch();
    $dailyWithdrawn = $dailyResult['daily_withdraw_amount'] ?? 0;
    $remainingDaily = $config['winypay']['daily_withdraw_limit'] - $dailyWithdrawn;

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'wallet' => [
            'balance' => (float)$wallet['balance'],
            'bonus' => (float)$wallet['bonus'],
            'total_deposited' => (float)$wallet['total_deposited'],
            'total_withdrawn' => (float)$wallet['total_withdrawn'],
            'daily_limit' => $config['winypay']['daily_withdraw_limit'],
            'daily_withdrawn' => (float)$dailyWithdrawn,
            'remaining_daily_limit' => (float)$remainingDaily,
        ],
        'transactions' => $transactions,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
