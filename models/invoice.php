<?php
/**
 * Invoice Generation System
 */

require_once __DIR__ . '/orders.php';

/**
 * Generate invoice HTML for an order
 */
function generateInvoiceHTML($orderId) {
    $order = getOrderById($orderId);
    
    if (!$order) {
        return false;
    }
    
    $invoiceNumber = 'INV-' . $order['order_number'];
    $invoiceDate = date('F j, Y', strtotime($order['created_at']));
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invoice <?= htmlspecialchars($invoiceNumber) ?></title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .invoice-container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .invoice-header { border-bottom: 3px solid #FF7A3D; padding-bottom: 20px; margin-bottom: 30px; }
            .invoice-header h1 { color: #FF7A3D; font-size: 32px; margin-bottom: 10px; }
            .invoice-meta { display: flex; justify-content: space-between; margin-top: 20px; }
            .invoice-meta div { flex: 1; }
            .invoice-meta strong { display: block; margin-bottom: 5px; color: #333; }
            .billing-section { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 30px 0; }
            .section-title { font-size: 18px; color: #37C6B0; margin-bottom: 10px; border-bottom: 2px solid #37C6B0; padding-bottom: 5px; }
            .items-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
            .items-table th { background: #37C6B0; color: white; padding: 12px; text-align: left; }
            .items-table td { padding: 12px; border-bottom: 1px solid #ddd; }
            .items-table tr:hover { background: #f9f9f9; }
            .text-right { text-align: right; }
            .totals-section { margin-top: 30px; padding-top: 20px; border-top: 2px solid #ddd; }
            .total-row { display: flex; justify-content: space-between; padding: 8px 0; }
            .total-row.grand-total { font-size: 20px; font-weight: bold; color: #FF7A3D; border-top: 2px solid #FF7A3D; padding-top: 15px; margin-top: 10px; }
            .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 12px; }
            @media print {
                body { background: white; padding: 0; }
                .invoice-container { box-shadow: none; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="invoice-container">
            <div class="invoice-header">
                <h1>INVOICE</h1>
                <div class="invoice-meta">
                    <div>
                        <strong>Invoice Number:</strong> <?= htmlspecialchars($invoiceNumber) ?>
                    </div>
                    <div>
                        <strong>Order Number:</strong> <?= htmlspecialchars($order['order_number']) ?>
                    </div>
                    <div>
                        <strong>Date:</strong> <?= htmlspecialchars($invoiceDate) ?>
                    </div>
                </div>
            </div>
            
            <div class="billing-section">
                <div>
                    <div class="section-title">Bill To:</div>
                    <p><strong><?= htmlspecialchars($order['shipping_name'] ?? 'Customer') ?></strong></p>
                    <?php if (!empty($order['shipping_address'])): ?>
                        <p><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($order['shipping_city'])): ?>
                        <p><?= htmlspecialchars($order['shipping_city']) ?><?= !empty($order['shipping_region']) ? ', ' . htmlspecialchars($order['shipping_region']) : '' ?></p>
                    <?php endif; ?>
                    <?php if (!empty($order['shipping_phone'])): ?>
                        <p>Phone: <?= htmlspecialchars($order['shipping_phone']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($order['guest_email']) || !empty($order['user_email'])): ?>
                        <p>Email: <?= htmlspecialchars($order['guest_email'] ?? $order['user_email'] ?? '') ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="section-title">From:</div>
                    <p><strong>Gulio - City Companion</strong></p>
                    <p>Accra, Ghana</p>
                    <p>Email: support@gulio.com</p>
                </div>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] ?? [] as $item): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                <?php if (!empty($item['variant_name'])): ?>
                                    <br><small><?= htmlspecialchars($item['variant_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= $item['quantity'] ?></td>
                            <td class="text-right">GHS <?= number_format($item['unit_price'], 2) ?></td>
                            <td class="text-right">GHS <?= number_format($item['total_price'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="totals-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>GHS <?= number_format($order['subtotal'] ?? 0, 2) ?></span>
                </div>
                <?php if (!empty($order['tax_amount']) && $order['tax_amount'] > 0): ?>
                    <div class="total-row">
                        <span>VAT (12.5% inclusive):</span>
                        <span>GHS <?= number_format($order['tax_amount'], 2) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                    <div class="total-row">
                        <span>Discount:</span>
                        <span>-GHS <?= number_format($order['discount_amount'], 2) ?></span>
                    </div>
                <?php endif; ?>
                <div class="total-row">
                    <span>Shipping:</span>
                    <span><?= ($order['shipping_amount'] ?? 0) > 0 ? 'GHS ' . number_format($order['shipping_amount'], 2) : 'Free' ?></span>
                </div>
                <div class="total-row grand-total">
                    <span>Total Amount:</span>
                    <span>GHS <?= number_format($order['total_amount'] ?? 0, 2) ?></span>
                </div>
            </div>
            
            <?php if (!empty($order['payment_reference'])): ?>
                <div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;">
                    <strong>Payment Information:</strong><br>
                    Payment Reference: <?= htmlspecialchars($order['payment_reference']) ?><br>
                    Payment Method: <?= ucfirst(str_replace('_', ' ', $order['payment_method'] ?? 'paystack')) ?><br>
                    Payment Status: <strong><?= ucfirst($order['payment_status'] ?? 'pending') ?></strong>
                </div>
            <?php endif; ?>
            
            <div class="footer">
                <p>Thank you for your business!</p>
                <p>This is an automated invoice generated by Gulio - City Companion</p>
            </div>
            
            <div class="no-print" style="margin-top: 30px; text-align: center;">
                <button onclick="window.print()" style="padding: 10px 20px; background: #FF7A3D; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                    Print Invoice
                </button>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

/**
 * Send invoice via email (placeholder - requires email configuration)
 */
function sendInvoiceEmail($orderId, $email) {
    // This would require email configuration
    // For now, just return success
    Logger::info('Invoice email sent', ['order_id' => $orderId, 'email' => $email]);
    return ['success' => true];
}

