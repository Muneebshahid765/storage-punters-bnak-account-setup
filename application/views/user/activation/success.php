<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Activated</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        .header-bg {
            background-color: #007550;
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background-color: #d4edda;
            color: #28a745;
            font-size: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
        }
        .text-green {
            color: #005f40;
        }

        .bg-green {
            background-color: #005f40;
        }
        .btn-green {
            background-color: #007550;
            border: none;
        }
        .btn-green:hover {
            background-color: #005f40;
        }
        .text-green {
            color: #005f40;
        }
        .footer-note {
            font-size: 13px;
            color: #999;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="header-bg text-center p-4">
            <h2 class="mb-0"><i class="bi bi-check-circle-fill"></i> Account Activated</h2>
        </div>
        <div class="card-body text-center px-4">
            <div class="icon-circle mb-3 bg-green">
                <i class="bi bi-check-lg text-white"></i>
            </div>
            <h4 class="text-green">Welcome aboard!</h4>
            <p class="mt-2 text-muted">
                Your email has been verified and your account is now active.
            </p>
            <p class="text-muted">
                You can now log in and explore exciting auctions.
            </p>
            <a href="<?= base_url('login') ?>" class="btn btn-green text-white px-4 mt-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Go to Login
            </a>
        </div>
        <div class="text-center p-3 bg-light border-top footer-note">
            &copy; <?= date('Y') ?> Storage Punters. All rights reserved.
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
