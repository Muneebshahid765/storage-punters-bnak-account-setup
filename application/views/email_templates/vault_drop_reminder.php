<!DOCTYPE html>
<html>
<head>
    <title>Vault Drop Reminder</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        .header { background: #333; color: #ffc107; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; text-align: center; }
        .countdown-box { font-size: 24px; font-weight: bold; margin: 20px 0; color: #d9534f; }
        .btn { display: inline-block; padding: 12px 24px; background: #ffc107; color: #000; text-decoration: none; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ VAULT DROP IMMINENT!</h1>
        </div>
        <div class="content">
            <p>Hi <?php echo $name; ?>,</p>
            <p>The draw for <strong><?php echo $draw_title; ?></strong> is starting soon.</p>
            
            <div class="countdown-box">
                ONLY 1 HOUR LEFT!
            </div>
            
            <p>This is your last chance to grab entries before the Vault locks.</p>
            
            <br>
            <a href="<?php echo base_url('vault'); ?>" class="btn">Go to The Vault</a>
        </div>
    </div>
</body>
</html>
