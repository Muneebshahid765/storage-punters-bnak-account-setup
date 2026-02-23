<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Welcome to Storage Punters</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e3e3e3; border-radius: 8px;">
        <tr>
            <td style="padding: 20px 30px; text-align: center; background-color: #007550; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h2 style="color: #ffffff; margin: 0;">Welcome to Storage Punters!</h2>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333;">Dear <strong><?= $name ?></strong>,</p>

                <p style="font-size: 16px; color: #333;">
                    Welcome to <strong>Storage Punters</strong>! We're excited to have your storage facility join our growing platform.
                    As a facility owner, you can easily manage your company profile, add facilities, and create auctions to connect with thousands of interested bidders nationwide.
                </p>

                <p style="font-size: 16px; color: #333;">
                    To get started, please set up your payment details and complete your company profile by clicking the button below:
                </p>

                <p style="text-align: center; margin: 20px 0;">
                    <a href="<?= $redirect_link ?>" style="display: inline-block; padding: 12px 20px; background-color: #007550; color: #fff; text-decoration: none; border-radius: 6px;">
                        Set Up Your Payment & Company Details
                    </a>
                </p>

                <p style="font-size: 16px; color: #333;">
                    Once your setup is complete, you’ll be ready to add your facilities and start posting auctions.
                </p>

                <p style="font-size: 16px; color: #333;">
                    We're thrilled to partner with you and help your facility reach new bidders every day.
                </p>

                <p style="font-size: 16px; color: #333;">
                    Best regards,<br>
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