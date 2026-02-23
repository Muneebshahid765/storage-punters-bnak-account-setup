<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Auction Has Ended!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%"
        style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e3e3e3; border-radius: 8px;">

        <!-- Header -->
        <tr>
            <td style="padding: 20px 30px; text-align: center; background-color: #007550; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h2 style="color: #ffffff; margin: 0;">Your Auction Has Ended!</h2>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333;">Dear <strong><?= $company_name ?></strong>,</p>

                <p style="font-size: 16px; color: #333;">
                    Congratulations! 🎉 Your storage auction <strong><?= $auction_ref ?></strong>, located at
                    <strong><?= $facility_name ?></strong>, has been successfully sold on <strong>Storage Punters</strong>
                    for <strong>$<?= $winning_bid_price ?></strong>.
                </p>

                <p style="font-size: 16px; color: #333;">
                    We’re pleased to confirm that the winning bid has been fully processed, and you can now release
                    the unit’s contents to the winning bidder. Your payment will be automatically transferred shortly.
                </p>

                <!-- What Happens Next -->
                <h3 style="font-size: 18px; color: #007550; margin-top: 35px;">What Happens Next</h3>
                <p style="font-size: 16px; color: #333;">
                    Please contact the buyer during business hours to arrange a suitable collection date and time.
                    You’ll find their contact details below.
                </p>
                <ul style="font-size: 16px; color: #333; margin-left: 20px;">
                    <li>A valid <strong>photo ID</strong>.</li>
                    <li>The required <strong>cleaning deposit (up to your discretion).</strong></li>
                </ul>

                <!-- Sale Summary -->
                <h3 style="font-size: 18px; color: #007550; margin-top: 35px;">Sale Summary</h3>
                <table width="100%" cellpadding="8" cellspacing="0"
                    style="border-collapse: collapse; font-size: 16px; color: #333;">
                    <tr style="background-color: #f9f9f9;">
                        <th align="left">Description</th>
                        <th align="right">Amount (AUD)</th>
                    </tr>
                    <tr>
                        <td>Sold Price (ex GST)</td>
                        <td align="right">$<?= $sold_price_ex_gst ?></td>
                    </tr>
                    <tr>
                        <td>GST (10%)</td>
                        <td align="right">$<?= $gst_amount ?></td>
                    </tr>
                    <tr style="font-weight: bold; border-top: 2px solid #ddd;">
                        <td>Final Price</td>
                        <td align="right">$<?= $final_price ?></td>
                    </tr>
                </table>

                <!-- Winning Bidder Details -->
                <h3 style="font-size: 18px; color: #007550; margin-top: 35px;">Winning Bidder Details</h3>
                <p style="font-size: 16px; color: #333;">
                    <strong>Name:</strong> <?= $bidder_name ?><br>
                    <strong>Email Address:</strong> <?= $bidder_email ?><br>
                    <strong>Phone:</strong> <?= $bidder_phone ?>
                </p>

                <!-- View Auction Button -->
                <p style="text-align: center; margin: 30px 0;">
                    <a href="<?= $auction_url ?>" style="background-color: #007550; color: #fff; padding: 12px 25px;
                    text-decoration: none; border-radius: 5px; display: inline-block;">
                        View Auction Details
                    </a>
                </p>

                <p style="font-size: 16px; color: #333;">
                    Thank you for choosing <strong>Storage Punters!</strong><br>
                    We appreciate your participation and look forward to hosting your future auctions.
                </p>

                <p style="font-size: 16px; color: #333;">
                    Best regards,<br>
                    <strong>The Storage Punters Team</strong>
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f1f1f1; text-align: center; padding: 15px; font-size: 14px; color: #888;
                       border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                &copy; <?= date('Y') ?> Storage Punters. All rights reserved.
            </td>
        </tr>
    </table>
</body>

</html>