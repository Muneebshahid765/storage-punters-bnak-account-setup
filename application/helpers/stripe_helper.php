<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('stripe_env_value')) {
    function stripe_env_value($key)
    {
        // Try getenv first
        $value = getenv($key);
        if ($value !== false && $value !== null && $value !== '') {
            return $value;
        }

        // Try $_ENV
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        // Try $_SERVER
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }

        // Fallback: Load directly from .env file if exists
        static $env_cache = null;
        if ($env_cache === null) {
            $env_file = FCPATH . '.env';
            if (file_exists($env_file)) {
                $env_cache = [];
                $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0) continue;
                    if (strpos($line, '=') !== false) {
                        list($k, $v) = explode('=', $line, 2);
                        $env_cache[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
                    }
                }
            } else {
                $env_cache = [];
            }
        }

        if (isset($env_cache[$key]) && $env_cache[$key] !== '') {
            return $env_cache[$key];
        }

        return null;
    }
}

if (!function_exists('stripe_secret_key')) {
    function stripe_secret_key()
    {
        return stripe_env_value('STRIPE_SECRET_KEY') ?: stripe_env_value('STRIPE_SECRET_KEY_TEST');
    }
}

if (!function_exists('stripe_publishable_key')) {
    function stripe_publishable_key()
    {
        return stripe_env_value('STRIPE_PUBLISHABLE_KEY') ?: stripe_env_value('STRIPE_PUBLISHABLE_KEY_TEST');
    }
}

if (!function_exists('stripe_webhook_secret')) {
    function stripe_webhook_secret()
    {
        return stripe_env_value('STRIPE_WEBHOOK_SECRET');
    }
}
