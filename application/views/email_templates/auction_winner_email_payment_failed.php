<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Action Required: Payment Pending for Your Auction Win</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"
        style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e3e3e3; border-radius: 8px;">
        <!-- Header -->
        <tr>
            <td style="padding: 20px 30px; text-align: center; background-color: #cc0000; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h2 style="color: #ffffff; margin: 0;">Action Required: Payment Pending</h2>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333;">Dear <strong><?= $name ?></strong>,</p>

                <p style="font-size: 16px; color: #333;">
                    Great news! You’ve successfully won the auction for <strong><?= $auction_item ?></strong>.
                </p>

                <p style="font-size: 16px; color: #333;">
                    However, it looks like your payment didn’t go through. To complete your purchase and secure your
                    shed, please log in and finalize your payment.
                </p>

                <!-- Deposit Details -->
                <table role="presentation" width="100%" style="margin: 20px 0; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; font-size: 16px; color: #333;">
                            <strong>Deposit:</strong> $<?= $deposit_amount ?: '0.00' ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 16px; color: #333;">
                            <strong>Deposit Type:</strong> <?= $deposit_type ?: 'N/A' ?>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 16px; color: #333;">
                    Please complete your payment within <strong>48 hours</strong> to prevent the item from being relisted.
                </p>

                <!-- Confirm Auction Button -->
                <p style="text-align: center; margin: 30px 0;">
                    <a href="<?= base_url('show-dashboard/dashboard') ?>"
                        style="background-color: #007550; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Login & Complete Payment
                    </a>
                </p>

                <p style="font-size: 16px; color: #333;">
                    If the button above doesn’t work, you can complete your payment by visiting this link:
                    <a href="<?= base_url('show-dashboard/dashboard') ?>" style="color: #007550; text-decoration: none;">
                        <?= base_url('show-dashboard/dashboard') ?>
                    </a>
                </p>

                <p style="font-size: 16px; color: #333;">
                    Thank you for participating — once your payment is confirmed, we’ll send your pickup or delivery
                    instructions right away.
                </p>

                <p style="font-size: 16px; color: #333;">
                    Cheers,<br>
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
