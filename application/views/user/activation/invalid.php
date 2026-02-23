<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invalid Activation Link</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
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
            background-color: #f8d7da;
            color: #dc3545;
            font-size: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
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

        .bg-green {
            background-color: #005f40;
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
            <h2 class="mb-0"><i class="bi bi-shield-exclamation"></i> Invalid Activation Link</h2>
        </div>
        <div class="card-body text-center px-4">
            <div class="icon-circle mb-3 bg-green">
                <i class="bi bi-x-circle-fill text-white"></i>
            </div>
            <h4 class="text-green">Oops! This link is no longer valid.</h4>
            <p class="mt-2 text-muted">
                The activation link you used is either expired, already used, or invalid. For your account's safety, we only allow one-time activation links.
            </p>
            <p class="text-muted">
                Please return to the homepage or request a new verification email from your account dashboard.
            </p>
            <a href="<?= base_url() ?>" class="btn btn-green text-white px-4 mt-3">
                <i class="bi bi-house-door-fill me-2"></i>Return to Homepage
            </a>
        </div>
        <div class="text-center p-3 bg-light border-top footer-note">
            &copy; <?= date('Y') ?> Storage Punters. All rights reserved.
        </div>
    </div>
</div>

<!-- Optional JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
