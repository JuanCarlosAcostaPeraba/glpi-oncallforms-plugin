<?php

declare(strict_types=1);

if (!function_exists('__')) {
    function __(string $message, ?string $domain = null): string
    {
        return $message;
    }
}
