<!DOCTYPE html>
<html>
<head>
    <title>Congratulations! You Won!</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; border: 2px solid #EAB308; }
        .header { background: #EAB308; padding: 30px; text-align: center; color: #000; }
        .content { padding: 30px; text-align: center; }
        .prize-box { background: #f8f9fa; border: 1px dashed #333; padding: 20px; margin: 20px 0; font-size: 18px; }
        .btn { display: inline-block; padding: 15px 30px; background: #000; color: #EAB308; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">🎉 YOU WON! 🎉</h1>
        </div>
        <div class="content">
            <h2>Congratulations, <?php echo $name; ?>!</h2>
            <p>We are thrilled to announce that you are the official winner of:</p>
            
            <h3 style="color: #EAB308; font-size: 24px; margin: 10px 0;"><?php echo $draw_title; ?></h3>
            
            <div class="prize-box">
                <strong>Winning Ticket:</strong> <?php echo $punter_id; ?><br>
                <strong>Prize Value:</strong> $<?php echo number_format($prize_value / 100); ?>
            </div>
            
            <p>Our team will be in touch with you shortly to arrange the delivery of your prize.</p>
            <p>Thank you for being a valued member of Storage Punters!</p>
            
            <a href="<?php echo base_url('vault'); ?>" class="btn">View Your Win</a>
        </div>
    </div>
</body>
</html>
