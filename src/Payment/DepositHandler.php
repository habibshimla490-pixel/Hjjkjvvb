<?php
/**
 * WinyPay Deposit Handler
 * Handles deposit initiation and processing
 */

namespace App\Payment;

use App\Database\Connection;
use App\Logger\PaymentLogger;

class DepositHandler
{
    private $config;
    private $db;
    private $logger;

    public function __construct($config, PaymentLogger $logger)
    {
        $this->config = $config['winypay'];
        $this->db = Connection::getInstance()->getConnection();
        $this->logger = $logger;
    }

    /**
     * Initiate deposit request
     */
    public function initiateDeposit($userId, $amount, $paymentMethod)
    {
        try {
            // Validate inputs
            $this->validateDepositRequest($userId, $amount, $paymentMethod);

            // Generate unique order ID
            $orderId = $this->generateOrderId('DEP');

            // Start database transaction
            $this->db->beginTransaction();

            try {
                // Save transaction record in PENDING state
                $transactionData = [
                    'user_id' => $userId,
                    'transaction_type' => 'deposit',
                    'order_id' => $orderId,
                    'gateway' => 'winypay',
                    'payment_method' => $paymentMethod,
                    'amount' => $amount,
                    'status' => 'pending',
                ];

                $transactionId = $this->saveTransaction($transactionData);

                // Build WinyPay API request
                $apiPayload = $this->buildDepositApiRequest($orderId, $userId, $amount, $paymentMethod);

                // Log the request
                $this->logger->logApiRequest(
                    $this->config['base_url'] . $this->config['deposit_endpoint'],
                    'POST',
                    $apiPayload,
                    $orderId
                );

                // Call WinyPay API
                $response = $this->callWinyPayApi(
                    $this->config['base_url'] . $this->config['deposit_endpoint'],
                    $apiPayload
                );

                // Log the response
                $this->logger->logApiResponse(
                    $this->config['deposit_endpoint'],
                    $response['http_code'],
                    $response['body'],
                    $orderId,
                    $response['execution_time']
                );

                $responseData = json_decode($response['body'], true);

                if (!$responseData) {
                    throw new \Exception("Invalid JSON response from WinyPay");
                }

                // Update transaction with API response
                $this->updateTransactionApiResponse($transactionId, $responseData);

                if ($responseData['status'] === 'success') {
                    // Update with internal transaction ID
                    $this->updateTransaction($transactionId, [
                        'internal_txn_id' => $responseData['internal_txn_id'] ?? null,
                    ]);

                    $this->db->commit();

                    // Log successful initiation
                    $this->logger->logTransaction(
                        'deposit',
                        $orderId,
                        $responseData['internal_txn_id'] ?? '',
                        $userId,
                        $amount,
                        'processing',
                        ['pay_url' => $responseData['pay_url']]
                    );

                    return [
                        'success' => true,
                        'order_id' => $orderId,
                        'internal_txn_id' => $responseData['internal_txn_id'],
                        'pay_url' => $responseData['pay_url'],
                        'message' => 'Redirect user to pay_url to complete payment',
                    ];
                } else {
                    // API returned error
                    $this->updateTransaction($transactionId, [
                        'status' => 'failed',
                        'error_message' => $responseData['message'] ?? 'Unknown error',
                    ]);

                    $this->db->commit();

                    $this->logger->logError(
                        'Deposit API returned error',
                        $responseData['message'],
                        ['order_id' => $orderId, 'response' => $responseData]
                    );

                    return [
                        'success' => false,
                        'message' => $responseData['message'] ?? 'Payment initiation failed',
                        'order_id' => $orderId,
                    ];
                }
            } catch (\Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->logger->logError('Deposit initiation failed', $e->getMessage(), [
                'user_id' => $userId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
            ]);

            return [
                'success' => false,
                'message' => 'Deposit initiation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process deposit callback from WinyPay
     */
    public function processDepositCallback($callbackData)
    {
        try {
            // Get transaction by order ID
            $transaction = $this->getTransactionByOrderId($callbackData['order_id']);

            if (!$transaction) {
                throw new \Exception("Transaction not found for order: " . $callbackData['order_id']);
            }

            // Validate callback data
            if (!$this->validateCallbackAmount($transaction['amount'], $callbackData['amount'])) {
                throw new \Exception("Amount mismatch in callback");
            }

            $this->db->beginTransaction();

            try {
                if ($callbackData['status'] === 'success') {
                    // Credit wallet
                    $this->creditWallet($transaction['user_id'], $callbackData['amount']);

                    // Update transaction
                    $this->updateTransaction($transaction['id'], [
                        'transaction_id' => $callbackData['transaction_id'],
                        'status' => 'success',
                    ]);

                    $this->db->commit();

                    $this->logger->logTransaction(
                        'deposit',
                        $transaction['order_id'],
                        $callbackData['transaction_id'],
                        $transaction['user_id'],
                        $callbackData['amount'],
                        'success'
                    );

                    return [
                        'success' => true,
                        'message' => 'Deposit processed successfully',
                    ];
                } else {
                    // Payment failed
                    $this->updateTransaction($transaction['id'], [
                        'status' => 'failed',
                        'error_message' => $callbackData['message'] ?? 'Payment declined',
                    ]);

                    $this->db->commit();

                    $this->logger->logTransaction(
                        'deposit',
                        $transaction['order_id'],
                        $callbackData['transaction_id'] ?? '',
                        $transaction['user_id'],
                        $callbackData['amount'],
                        'failed'
                    );

                    return [
                        'success' => true,
                        'message' => 'Payment declined',
                    ];
                }
            } catch (\Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->logger->logError('Deposit callback processing failed', $e->getMessage(), [
                'callback_data' => $callbackData,
            ]);

            return [
                'success' => true, // Return success to prevent WinyPay retries, but log the error
                'message' => 'Callback processed with errors',
            ];
        }
    }

    /**
     * Validate deposit request
     */
    private function validateDepositRequest($userId, $amount, $paymentMethod)
    {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new \Exception("Invalid user ID");
        }

        if (!is_numeric($amount) || $amount < $this->config['min_deposit']) {
            throw new \Exception("Amount must be at least " . $this->config['min_deposit']);
        }

        if ($amount > $this->config['max_deposit']) {
            throw new \Exception("Amount exceeds maximum limit of " . $this->config['max_deposit']);
        }

        if (!array_key_exists($paymentMethod, $this->config['payment_methods'])) {
            throw new \Exception("Invalid payment method");
        }
    }

    /**
     * Build WinyPay deposit API request
     */
    private function buildDepositApiRequest($orderId, $userId, $amount, $paymentMethod)
    {
        return [
            'merchant_code' => $this->config['merchant_code'],
            'secret_key' => $this->config['secret_key'],
            'order_id' => $orderId,
            'user_id' => 'USER' . $userId,
            'amount' => number_format($amount, 2, '.', ''),
            'pay_type' => $paymentMethod,
            'current_time' => date('Y-m-d H:i:s'),
            'jump_url' => $this->config['deposit_return_url'],
            'callback_url' => $this->config['deposit_callback_url'],
        ];
    }

    /**
     * Call WinyPay API
     */
    private function callWinyPayApi($url, $payload)
    {
        $ch = curl_init();
        $startTime = microtime(true);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => $this->config['request_timeout'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        $executionTime = round((microtime(true) - $startTime) * 1000);

        if ($error) {
            throw new \Exception("cURL Error: " . $error);
        }

        return [
            'http_code' => $httpCode,
            'body' => $response,
            'execution_time' => $executionTime,
        ];
    }

    /**
     * Generate unique order ID
     */
    private function generateOrderId($prefix)
    {
        return $prefix . '-' . date('YmdHis') . '-' . random_int(100000, 999999);
    }

    /**
     * Save transaction to database
     */
    private function saveTransaction($data)
    {
        $keys = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));

        $query = "INSERT INTO payment_transactions ($keys) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array_values($data));

        return $this->db->lastInsertId();
    }

    /**
     * Update transaction
     */
    private function updateTransaction($transactionId, $data)
    {
        $setClause = implode(',', array_map(fn($k) => "$k = ?", array_keys($data)));
        $values = array_values($data);
        $values[] = $transactionId;

        $query = "UPDATE payment_transactions SET $setClause WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute($values);
    }

    /**
     * Update transaction with API response
     */
    private function updateTransactionApiResponse($transactionId, $responseData)
    {
        $query = "UPDATE payment_transactions SET api_response_payload = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([json_encode($responseData), $transactionId]);
    }

    /**
     * Get transaction by order ID
     */
    private function getTransactionByOrderId($orderId)
    {
        $query = "SELECT * FROM payment_transactions WHERE order_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$orderId]);

        return $stmt->fetch();
    }

    /**
     * Credit wallet
     */
    private function creditWallet($userId, $amount)
    {
        // Check if wallet exists, if not create it
        $query = "INSERT INTO user_wallets (user_id, balance) VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE balance = balance + ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $amount, $amount]);
    }

    /**
     * Validate callback amount matches transaction amount
     */
    private function validateCallbackAmount($expectedAmount, $callbackAmount)
    {
        return abs(floatval($expectedAmount) - floatval($callbackAmount)) < 0.01;
    }
}
