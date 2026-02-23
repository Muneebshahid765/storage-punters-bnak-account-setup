<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your OTP Code - Storage Punters</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e3e3e3; border-radius: 8px;">
        <!-- Header -->
        <tr>
            <td style="padding: 20px 30px; text-align: center; background-color: #007550; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h2 style="color: #ffffff; margin: 0;">Your One-Time Password (OTP)</h2>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333;">Hi <strong><?= $name ?></strong>,</p>

                <p style="font-size: 16px; color: #333;">
                    To complete your registration with <strong>Storage Punters</strong>, please use the following OTP (One-Time Password) to verify your email address:
                </p>

                <p style="font-size: 28px; color: #007550; font-weight: bold; text-align: center; margin: 30px 0;">
                    <?= $otp_code ?>
                </p>

                <p style="font-size: 16px; color: #333;">
                    This code will expire in 10 minutes. If you didn’t request this, you can safely ignore this email.
                </p>

                <p style="font-size: 16px; color: #333;">
                    Thank you for joining us!
                </p>

                <p style="font-size: 16px; color: #333;">
                    Best regards,<br>
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