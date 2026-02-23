<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Invoice from Storage Punters</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e3e3e3; border-radius: 8px;">
        <!-- Header -->
        <tr>
            <?php
            // Determine background color based on payment status
            $headerBgColor = (isset($invoice_status) && strtolower($invoice_status) === 'paid')
                ? '#007550'   // Green for paid
                : '#cc0000';  // Red for unpaid
            ?>
            <td style="padding: 20px 30px; text-align: center; background-color: <?= $headerBgColor ?>; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h2 style="color: #ffffff; margin: 0;">
                    <?= (strtolower($invoice_status) === 'paid')
                        ? 'Your Invoice from Storage Punters (Paid)'
                        : 'Your Invoice from Storage Punters (Unpaid)' ?>
                </h2>
            </td>

        </tr>

        <!-- Body -->
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333;">Hello <strong><?= $name ?></strong>,</p>

                <p style="font-size: 16px; color: #333;">
                    This is an automatically generated invoice from <strong>Storage Punters</strong>.
                </p>

                <p style="font-size: 16px; color: #333;">
                    Attached is the invoice for your winning auction item: <strong><?= $auction_item ?></strong>.
                </p>

                <!-- Invoice Summary -->
                <table cellpadding="6" cellspacing="0" width="100%" style="border-collapse: collapse; margin-top: 20px; font-size: 15px; color: #333;">
                    <tr>
                        <td style="border-bottom: 1px solid #ddd;">Invoice Number:</td>
                        <td style="border-bottom: 1px solid #ddd;"><strong><?= $invoice_number ?></strong></td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #ddd;">Auction Amount:</td>
                        <td style="border-bottom: 1px solid #ddd;">$<?= number_format($auction_amount, 2) ?></td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #ddd;"><?= $commission_text ?>:</td>
                        <td style="border-bottom: 1px solid #ddd;">$<?= number_format($auction_commission_amount, 2) ?></td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #ddd;">Tax on Invoice (10%):</td>
                        <td style="border-bottom: 1px solid #ddd;">$<?= number_format($auction_tax_amount, 2) ?></td>
                    </tr>
                    <tr>
                        <td style="border-top: 2px solid #000; font-weight: bold;">Total Payable:</td>
                        <td style="border-top: 2px solid #000; font-weight: bold;">$<?= number_format($auction_total, 2) ?></td>
                    </tr>
                </table>

                <!-- Payment Instructions -->
                <p style="font-size: 16px; color: #333; margin-top: 25px;">
                    The total amount will be automatically deducted from your <strong>default payment method</strong>.
                    If the payment fails, you can manually complete it by following these steps:
                </p>

                <ol style="font-size: 16px; color: #333; padding-left: 20px;">
                    <li>Log in to your account on the <strong>Storage Punters</strong> platform.</li>
                    <li>Navigate to <strong>Dashboard</strong> → <strong>Invoices</strong>.</li>
                    <li>Click the three dots <strong>(⋯)</strong> next to your invoice and select <strong>Pay Now</strong>.</li>
                    <li>Confirm the payment by clicking <strong>Pay Now</strong> again.</li>
                </ol>

                <p style="font-size: 16px; color: #333;">
                    If you have any questions about your invoice or payment, please <a href="<?= base_url('contact') ?>" style="color: #007550; text-decoration: none;">contact our support team</a>.
                </p>

                <p style="font-size: 16px; color: #333;">
                    Thank you for choosing <strong>Storage Punters</strong>!
                </p>

                <p style="font-size: 16px; color: #333;">
                    Sincerely,<br>
                    <strong>The Storage Punters Team</strong>
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f1f1f1; text-align: center; padding: 15px; font-size: 14px; color: #888; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                &copy; <?= date('Y') ?> Storage Punters. All rights reserved.
            </td>
        </tr>
    </table>
</body>

</html>