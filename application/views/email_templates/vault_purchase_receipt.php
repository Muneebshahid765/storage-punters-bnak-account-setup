<!DOCTYPE html>
<html>
<head>
    <title>Purchase Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; border-top: 4px solid #EAB308; }
        .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .content { padding: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f9f9f9; }
        .total-row { font-weight: bold; font-size: 16px; background-color: #fffbe6; }
        .footer { text-align: center; color: #777; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="color: #EAB308; margin:0;">Receipt</h2>
            <p>Thank you for your purchase!</p>
        </div>
        <div class="content">
            <p>Hi <?php echo $name; ?>,</p>
            <p>We've received your payment for <strong><?php echo $package_title; ?></strong>. Your coins and entries have been added to your account.</p>
            
            <table>
                <tr>
                    <th>Invoice #</th>
                    <td><?php echo $invoice_number; ?></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td><?php echo date('F j, Y'); ?></td>
                </tr>
                <tr>
                    <th>Items</th>
                    <td><?php echo $entries; ?> Entries</td>
                </tr>
                <tr class="total-row">
                    <th>Total Paid</th>
                    <td>$<?php echo number_format($amount, 2); ?> AUD</td>
                </tr>
            </table>
            
            <p style="text-align: center; margin-top: 30px;">
                <a href="<?php echo base_url('vault'); ?>" style="background: #000; color: #EAB308; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Go to The Vault</a>
            </p>
        </div>
        <div class="footer">
            <p>Storage Punters</p>
        </div>
    </div>
</body>
</html>
