<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Storage Punters Invoice</title>
</head>

<body style="font-family: Arial, sans-serif; font-size: 14px; padding: 0; margin: 0; color: #333;">
    <div style="width: 100%; max-width: 700px; margin: 0 auto; padding: 20px;">

        <!-- Header -->
        <table width="100%" style="border-bottom: 2px solid #007550; padding-bottom: 10px; margin-bottom: 20px;">
            <tr>
                <td style="width: 50%;">
                    <img src="<?= base_url('assets/images/logo2.jpeg') ?>" alt="Storage Punters Logo" style="height: 60px;">
                </td>
                <td style="width: 50%; text-align: right;">
                    <h2 style="margin: 0; color: #007550;">Invoice <?= $invoice_number ?></h2>
                    <p style="margin: 0;"><strong>Date:</strong> <?= date('F j, Y') ?></p>
                    <p style="margin: 0;"><strong>Status:</strong>
                        <?php if ($invoice_status === 'Paid'): ?>
                            <span style="color: #007550; font-weight: bold;">Paid</span>
                        <?php else: ?>
                            <span style="color: #dc3545; font-weight: bold;">Unpaid</span>
                        <?php endif; ?>
                    </p>
                </td>
            </tr>
        </table>

        <!-- Billed To -->
        <h4 style="margin-top: 0; color: #007550;">Billed To:</h4>
        <p style="margin: 5px 0 20px 0;">
            <?= htmlspecialchars($name) ?><br>
            <?= isset($email) ? htmlspecialchars($email) : '' ?>
        </p>

        <!-- Auction Info -->
        <h4 style="margin-bottom: 10px; color: #007550;">Auction Information</h4>
        <p style="margin: 5px 0 20px 0;">
            <strong>Auction:</strong> <?= htmlspecialchars($auction_item) ?>
        </p>

        <!-- Summary Table -->
        <h4 style="margin-bottom: 10px; color: #007550;">Summary</h4>
        <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; border: 1px solid #ccc;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th align="left" style="border: 1px solid #ccc;">Description</th>
                    <th align="right" style="border: 1px solid #ccc;">Base Amount</th>
                    <th align="right" style="border: 1px solid #ccc;"><?= htmlspecialchars($commission_text) ?></th>
                    <th align="right" style="border: 1px solid #ccc;">Tax on Invoice (10%)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid #ccc;"><?= htmlspecialchars($auction_item) ?></td>
                    <td align="right" style="border: 1px solid #ccc;">$<?= number_format($auction_amount, 2) ?></td>
                    <td align="right" style="border: 1px solid #ccc;">$<?= number_format($auction_commission_amount, 2) ?></td>
                    <td align="right" style="border: 1px solid #ccc;">$<?= number_format($auction_tax_amount, 2) ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr style="background-color: #f9f9f9;">
                    <td colspan="3" align="right" style="border: 1px solid #ccc;"><strong>Total Payable:</strong></td>
                    <td align="right" style="border: 1px solid #ccc;"><strong>$<?= number_format($auction_total, 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <!-- Payment Info -->
        <?php if ($invoice_status === 'Paid'): ?>
            <p style="margin-top: 20px; font-size: 14px;">
                <strong>Payment Method:</strong> <?= isset($payment_method) ? htmlspecialchars($payment_method) : 'Stripe (Default)' ?><br>
                <strong>Payment Date:</strong> <?= isset($payment_date) ? htmlspecialchars($payment_date) : date('F j, Y, g:i A') ?><br>
                <strong>Transaction ID:</strong> <?= isset($transaction_id) ? htmlspecialchars($transaction_id) : '-' ?>
            </p>
        <?php else: ?>
            <p style="margin-top: 20px; font-size: 14px; color: #dc3545;">
                <?= htmlspecialchars($payment_status_text) ?>
            </p>
        <?php endif; ?>

        <!-- Footer -->
        <p style="text-align: center; margin-top: 40px; font-size: 13px; color: #666;">
            Thank you for your business with <strong>Storage Punters</strong>!<br>
            <?= $invoice_status === 'Paid'
                ? 'This payment was processed automatically via your default Stripe payment method.'
                : 'Please complete your payment at your earliest convenience to finalize this transaction.' ?>
        </p>
    </div>
</body>

</html>