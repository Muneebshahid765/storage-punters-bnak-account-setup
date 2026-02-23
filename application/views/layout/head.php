<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storage Punters</title>
    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('assets/images/logo2.jpeg') ?>" type="image/x-icon">
    <meta name="description" content="Bid on self-storage auctions online! Storage Punters is a free storage auction platform that makes it easy to find and bid on storage units!">
    <meta name="robots" content="index,follow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@storagepunters">
    <meta name="twitter:creator" content="@storagepunters">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Bid on Online Storage Unit Auctions - Storage Punters">
    <meta property="og:description" content="Bid on self-storage auctions online! Storage Punters is a free storage auction platform that makes it easy to find and bid on storage units!">
    <meta property="og:url" content="https://storagepunters.com">
    <meta property="og:type" content="website">
    <meta property="og:image" content="https://storagepunters.com/banner1.png">
    <meta property="og:image:alt" content="Search for online self-storage unit auctions">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="en_US">
    <meta property="og:site_name" content="Storage Punters">

    <!-- Additional Meta -->
    <meta name="author" content="storagepunters.com">
    <link rel="canonical" href="https://storagepunters.com">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>" as="style">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>" as="style">
    <link rel="stylesheet" href="<?= base_url('assets/css/home_modern.css') ?>" as="style">
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard_modern.css') ?>" as="style">
    <link rel="stylesheet" href="<?= base_url('assets/css/vault.css') ?>" as="style">
    <link rel="stylesheet" href="<?= base_url('assets/css/modern-redesign.css') ?>" as="style">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- Bootstrap Switch CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap3/bootstrap-switch.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <!-- Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

    <!-- Fancybox -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/countdown.css') ?>" as="style">
    <link rel="stylesheet" href="<?= base_url('assets/css/subscription.css') ?>" as="style">
</head>

<body style="--border-color: #1C421D; --icon-yellow: #1C421D; --bg-primary-light: #007550; --text-yello-light: #1C421D; --bg-list-: #007550; --bg-register-active: #007550; --bg-primary-color: #1C421D; --btn-bg-yellow: #1C421D; --ssa-color-dark: #1C421D; --ssa-color-light: #007550; --border-active: #1C421D; --primary-dark: #1C421D; --color-high-bid: #007550; --timer-day: #7a2b269c; --timer-hour: #7a2b26c4; --timer-minute: #7a2b26d6; --timer-second: #6f0e08c8; --timer-border: #957a67d4;">


    <!-- Countdown Container -->
    <div class="container text-center" id="title" style="max-width:100%">
        <h1 class="p-1 p-md-3 text-center" style="font-weight: bold; color: #666;">Bidding Starts In</h1>
        <div id="countdown"></div>
        <p id="note"></p>
    </div>

    <div class="alertNotification d-none fs-4">
        <span id="alertIcon" style="margin-right: 10px;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="green" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM7.97 11.03a.75.75 0 0 0 1.06 0l3.536-3.536a.75.75 0 1 0-1.06-1.06L8.5 9.44l-1.94-1.94a.75.75 0 1 0-1.06 1.06l2.47 2.47z" />
            </svg>
        </span>
        <span id="alertMessage"></span>
    </div>

    <div id="table-skeleton-overlay" class="table-skeleton-overlay" style="display: none;"></div>

    <div id="table-loader" class="table-loader text-center" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;">
        <div class="spinner-border text-customer-primary-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>