<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Congratulations, You Won the Auction!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"
        style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e3e3e3; border-radius: 8px;">
        <!-- Header -->
        <tr>
            <td
                style="padding: 20px 30px; text-align: center; background-color: #007550; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h2 style="color: #ffffff; margin: 0;">Congratulations, You Won!</h2>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333;">Dear <strong><?= $name ?></strong>,</p>

                <p style="font-size: 16px; color: #333;">
                    Fantastic news! Your payment has been successfully processed, and you are now the proud winner of
                    <strong><?= $auction_item ?></strong>.
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

                <!-- Storage Auction Details -->
                <h3 style="font-size: 18px; color: #007550; margin-top: 35px;">Facility Storage Details</h3>
                <p style="font-size: 16px; color: #333;">
                    <strong>Company Name:</strong> <?= $company_name ?><br>
                    <strong>Facility Name:</strong> <?= $facility_name ?><br>
                    <strong>Contact Name:</strong> <?= $facility_contact_name ?><br>
                    <strong>Contact Email Address:</strong> <?= $facility_contact_email ?><br>
                    <strong>Contact Phone:</strong> <?= $facility_contact_phone ?>
                </p>

                <p style="font-size: 16px; color: #333;">
                    You can now coordinate with the facility to arrange pickup or delivery for your shed. Make sure to
                    bring a valid ID and any required documentation when collecting your unit.
                </p>

                <!-- Confirm Auction Button -->
                <p style="text-align: center; margin: 30px 0;">
                    <a href="<?= base_url('show-dashboard/dashboard') ?>"
                        style="background-color: #007550; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        View Your Dashboard
                    </a>
                </p>

                <p style="font-size: 16px; color: #333;">
                    If the button above doesn’t work, you can access your dashboard here:
                    <a href="<?= base_url('show-dashboard/dashboard') ?>" style="color: #007550; text-decoration: none;">
                        <?= base_url('show-dashboard/dashboard') ?>
                    </a>
                </p>

                <p style="font-size: 16px; color: #333;">
                    Thank you for your prompt payment and participation. We hope you enjoy your auction win!
                </p>

                <p style="font-size: 16px; color: #333;">
                    Cheers,<br>
                    <strong>The Storage Punters Team</strong>
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td
                style="background-color: #f1f1f1; text-align: center; padding: 15px; font-size: 14px; color: #888; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                &copy; <?= date('Y') ?> Storage Punters. All rights reserved.
            </td>
        </tr>
    </table>
</body>

</html>