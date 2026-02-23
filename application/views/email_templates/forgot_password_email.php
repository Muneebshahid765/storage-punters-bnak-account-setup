<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e3e3e3; border-radius: 8px;">
        <tr>
            <td style="padding: 20px 30px; text-align: center; background-color:  #007550; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h2 style="color: #ffffff; margin: 0;">Reset Your Password</h2>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333;">Hello <strong><?= $name ?></strong>,</p>

                <p style="font-size: 16px; color: #333;">
                    We received a request to reset your <strong>Storage Punters</strong> password.
                </p>

                <p style="font-size: 16px; color: #333;">Click the button below to set a new password:</p>

                <p style="text-align: center; margin: 30px 0;">
                    <a href="<?= $reset_link ?>" style="background-color: #007550; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Reset Password
                    </a>
                </p>

                <p style="font-size: 16px; color: #333;">
                    If you did not request a password reset, please ignore this email or contact our support team.
                </p>

                <p style="font-size: 16px; color: #333;">
                    Thank you,<br>
                    <strong>The Storage Punters Team</strong>
                </p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f1f1f1; text-align: center; padding: 15px; font-size: 14px; color: #888; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                &copy; <?= date('Y') ?> Storage Punters. All rights reserved.
            </td>
        </tr>
    </table>
</body>

</html>