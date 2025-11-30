<?php
/**
 * Payment Callback Page
 * Handles Paystack payment verification and booking confirmation
 */

// Get payment reference from URL
$reference = $_GET['reference'] ?? '';

if (!$reference) {
    header('Location: ?page=city-buddy&error=invalid_reference');
    exit;
}

try {
    $paystack = new PaystackPayment();
    $verification = $paystack->verifyPayment($reference);
    
    if ($verification['status'] && $verification['data']['status'] === 'success') {
        $paymentData = $verification['data'];
        $metadata = $paymentData['metadata'];
        
        // Extract booking details from metadata
        $buddyId = $metadata['buddy_id'] ?? 0;
        $userId = $metadata['user_id'] ?? 0;
        $hours = $metadata['hours'] ?? 0;
        $datetime = $metadata['datetime'] ?? '';
        $location = $metadata['location'] ?? '';
        $description = $metadata['description'] ?? '';
        $amount = $paymentData['amount'] / 100; // Convert from kobo
        
        // Log successful payment
        Logger::info('Buddy payment verified', [
            'reference' => $reference,
            'buddy_id' => $buddyId,
            'user_id' => $userId,
            'amount' => $amount,
            'payment_id' => $paymentData['id']
        ]);
        
        // Store booking in session for display (in production, save to database)
        $_SESSION['last_booking'] = [
            'type' => 'city_buddy',
            'buddy_id' => $buddyId,
            'hours' => $hours,
            'datetime' => $datetime,
            'location' => $location,
            'description' => $description,
            'amount' => $amount,
            'reference' => $reference,
            'status' => 'confirmed'
        ];
        
        $success = true;
        $message = 'Payment successful! Your city buddy booking has been confirmed.';
        
    } else {
        $success = false;
        $message = 'Payment verification failed. Please contact support if you were charged.';
        Logger::warning('Payment verification failed', [
            'reference' => $reference,
            'response' => $verification
        ]);
    }
    
} catch (Exception $e) {
    $success = false;
    $message = 'Payment verification error. Please contact support.';
    Logger::error('Payment callback error', [
        'reference' => $reference,
        'error' => $e->getMessage()
    ]);
}
?>

<div class="payment-callback-screen">
    <div class="callback-container">
        <?php if ($success): ?>
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Payment Successful!</h1>
            <p><?= htmlspecialchars($message) ?></p>
            
            <?php if (isset($_SESSION['last_booking'])): ?>
                <div class="booking-summary">
                    <h3>Booking Details</h3>
                    <div class="summary-item">
                        <span>Hours:</span>
                        <span><?= $_SESSION['last_booking']['hours'] ?> hour(s)</span>
                    </div>
                    <div class="summary-item">
                        <span>Date & Time:</span>
                        <span><?= date('M j, Y g:i A', strtotime($_SESSION['last_booking']['datetime'])) ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Location:</span>
                        <span><?= htmlspecialchars($_SESSION['last_booking']['location']) ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Amount Paid:</span>
                        <span>GHS <?= number_format($_SESSION['last_booking']['amount'], 2) ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Reference:</span>
                        <span><?= htmlspecialchars($_SESSION['last_booking']['reference']) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="callback-actions">
                <a href="?page=city-buddy" class="btn btn-outline">
                    <i class="fas fa-user-friends"></i> Back to City Buddy
                </a>
                <a href="?page=home" class="btn btn-primary">
                    <i class="fas fa-home"></i> Go Home
                </a>
            </div>
            
        <?php else: ?>
            <div class="error-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <h1>Payment Failed</h1>
            <p><?= htmlspecialchars($message) ?></p>
            
            <div class="callback-actions">
                <a href="?page=city-buddy" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Try Again
                </a>
                <a href="?page=home" class="btn btn-primary">
                    <i class="fas fa-home"></i> Go Home
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.payment-callback-screen {
    min-height: 100vh;
    background: var(--light-gray);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-lg);
}

.callback-container {
    background: var(--white);
    padding: var(--space-xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    text-align: center;
    max-width: 500px;
    width: 100%;
}

.success-icon {
    color: var(--verified-green);
    font-size: 64px;
    margin-bottom: var(--space-lg);
}

.error-icon {
    color: var(--error-red);
    font-size: 64px;
    margin-bottom: var(--space-lg);
}

.callback-container h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: var(--space-md);
}

.callback-container p {
    font-size: 16px;
    color: var(--text-medium);
    margin-bottom: var(--space-xl);
    line-height: 1.5;
}

.booking-summary {
    background: var(--light-gray);
    padding: var(--space-lg);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-xl);
    text-align: left;
}

.booking-summary h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-md);
    text-align: center;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-sm) 0;
    border-bottom: 1px solid var(--medium-gray);
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item span:first-child {
    font-weight: 500;
    color: var(--text-medium);
}

.summary-item span:last-child {
    font-weight: 600;
    color: var(--text-dark);
}

.callback-actions {
    display: flex;
    gap: var(--space-md);
    justify-content: center;
}

.callback-actions .btn {
    flex: 1;
    max-width: 200px;
}

@media (max-width: 480px) {
    .callback-actions {
        flex-direction: column;
    }
    
    .callback-actions .btn {
        max-width: none;
    }
}
</style>
