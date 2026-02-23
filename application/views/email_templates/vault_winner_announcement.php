<!DOCTYPE html>
<html>
<head>
    <title>The Vault Winner Announced!</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        .header { background: #ffc107; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; text-align: center; }
        .winner-box { background: #333; color: #ffc107; padding: 15px; font-size: 24px; font-weight: bold; margin: 20px 0; border-radius: 4px; font-family: monospace; }
        .btn { display: inline-block; padding: 12px 24px; background: #333; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏆 THE VAULT WINNER 🏆</h1>
        </div>
        <div class="content">
            <p>The draw for <strong><?php echo $draw_title; ?></strong> is complete!</p>
            <p>And the winner is...</p>
            
            <div class="winner-box">
                <?php echo $winner_name; ?>
            </div>
            
            <p>Winning Ticket: <strong><?php echo $punter_id; ?></strong></p>
            <p>Prize Value: <strong>$<?php echo number_format($prize_value / 100); ?></strong></p>
            
            <br>
            <p>Don't worry if you didn't win this time. Your coins are still valid for the next draw!</p>
            <br>
            
            <a href="<?php echo base_url('vault'); ?>" class="btn">View Results & Next Draw</a>
        </div>
    </div>
</body>
</html>
