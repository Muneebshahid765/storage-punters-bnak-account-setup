<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Verify Your Email Address</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e3e3e3; border-radius: 8px;">
        <tr>
            <td style="padding: 20px 30px; text-align: center; background-color: #007550; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h2 style="color: #ffffff; margin: 0;">Verify Your Email Address</h2>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333;">Hi <strong><?= $name ?></strong>,</p>

                <p style="font-size: 16px; color: #333;">
                    To complete your registration with <strong>Storage Punters</strong>, please verify your email address by clicking the button below:
                </p>

                <p style="text-align: center; margin: 30px 0;">
                    <a href="<?= $verify_link ?>" style="background-color: #007550; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Verify Email
                    </a>
                </p>

                <p style="font-size: 16px; color: #333;">
                    If you did not sign up for Storage Punters, please disregard this email.
                </p>

                <p style="font-size: 16px; color: #333;">
                    Thank you for joining us!<br>
                    Happy Punting,<br>
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