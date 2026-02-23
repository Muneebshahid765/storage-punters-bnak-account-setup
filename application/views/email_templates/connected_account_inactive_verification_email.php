<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Payment Account Is Pending — Please Complete Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e3e3e3; border-radius: 8px;">
        <!-- Header -->
        <tr>
            <td style="padding: 20px 30px; text-align: center; background-color: #cc0000; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h2 style="color: #ffffff; margin: 0;">Action Required: Verify Your Payment Account</h2>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333;">Dear <strong><?= $name ?></strong>,</p>

                <p style="font-size: 16px; color: #333;">
                    Your payment account is currently <strong>pending verification</strong>. To start receiving payouts for your auction sales, please complete your verification process with Stripe.
                </p>

                <p style="font-size: 16px; color: #333;">
                    It only takes a few minutes to complete. Once verified, your account will be active and ready to receive payments directly to your connected bank account.
                </p>

                <!-- Go to Verify My Account Button -->
                <p style="text-align: center; margin: 30px 0;">
                    <a href="<?= htmlspecialchars($onboarding_url ?? base_url('storage-facility/dashboard')) ?>" style="background-color: #007550; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Verify My Account
                    </a>
                </p>


                <p style="font-size: 16px; color: #333;">
                    If you’ve already completed verification, please allow a short time for your account status to update.
                </p>


                <p style="font-size: 16px; color: #333;">
                    Thank you for your cooperation,<br>
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