<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Registration Successful</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f2f4f7;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        .success-card {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .success-header {
            background-color: #007550;
            color: #fff;
            padding: 40px 20px 30px;
            text-align: center;
        }

        .success-header .bi {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .success-body {
            padding: 40px 30px;
            text-align: center;
        }

        .success-body h3 {
            color: #005f40;
            margin-bottom: 15px;
        }

        .success-body .lead {
            font-size: 1.1rem;
            color: #444;
            margin-bottom: 20px;
        }

        .highlight-box {
            background-color: #e9f7f0;
            color: #005f40;
            font-weight: 600;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .success-body ul {
            list-style: none;
            padding: 0;
            margin: 20px 0;
            color: #555;
            font-size: 0.95rem;
        }

        .success-body ul li {
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
        }

        .success-body ul li i {
            color: #007550;
            margin-right: 8px;
            margin-top: 2px;
        }

        .footer-note {
            font-size: 13px;
            color: #999;
            background: #f9f9f9;
            padding: 15px;
            border-top: 1px solid #eee;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="success-card">
        <div class="success-header">
            <i class="bi bi-check-circle-fill"></i>
            <h2 class="mt-2">Registration Successful</h2>
        </div>
        <div class="success-body">
            <h3>Welcome to Storage Punters!</h3>
            <p class="lead">
                Your account has been created successfully. To complete your registration, please verify your email address.
            </p>

            <?php if (!empty($user_email)): ?>
                <div class="highlight-box">
                    A confirmation email has been sent to:<br>
                    <span><?= htmlspecialchars($user_email) ?></span>
                </div>
            <?php endif; ?>

            <ul>
                <li><i class="bi bi-envelope"></i> Check your inbox for the verification email</li>
                <li><i class="bi bi-shield-lock"></i> Click the activation link to secure your account</li>
                <li><i class="bi bi-hourglass-split"></i> If not received in a few minutes, check your spam/junk folder</li>
            </ul>

            <p class="text-muted mt-4">
                After verification, you’ll be able to log in and participate in auctions.
            </p>

            <a href="<?= base_url('login') ?>" class="btn btn-success mt-3 px-4" style="background-color:#007550;border:none;">
                <i class="bi bi-box-arrow-in-right me-1"></i> Go to Login
            </a>

        </div>
        <div class="footer-note">
            &copy; <?= date('Y') ?> Storage Punters. All rights reserved.
        </div>
    </div>

</body>

</html>