<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Payment Account Is Verified — You’re Ready to Receive Payouts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e3e3e3; border-radius: 8px;">
        <!-- Header -->
        <tr>
            <td style="padding: 20px 30px; text-align: center; background-color: #007550; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h2 style="color: #ffffff; margin: 0;">Your Payment Account Is Now Active!</h2>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333;">Dear <strong><?= $name ?></strong>,</p>

                <p style="font-size: 16px; color: #333;">
                    Great news! Your payment account has been successfully verified and activated. You’re now ready to receive payouts from your auction sales directly to your connected bank account.
                </p>

                <p style="font-size: 16px; color: #333;">
                    You can view or manage your payment details anytime from your Storage Punters dashboard.
                </p>

                <!-- Go to My Dashboard Button -->
                <p style="text-align: center; margin: 30px 0;">
                    <a href="<?= htmlspecialchars($dashboard_url ?? base_url('storage-facility/dashboard')) ?>" style="background-color: #007550; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Go to My Dashboard
                    </a>
                </p>

                <p style="font-size: 16px; color: #333;">Thank you for verifying your account and being part of our trusted seller network.</p>

                <p style="font-size: 16px; color: #333;">
                    Best of luck,<br>
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
