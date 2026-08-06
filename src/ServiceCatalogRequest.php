<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms;

final class ServiceCatalogRequest
{
    public static function isCatalog(string $requestUri): bool
    {
        $path = parse_url($requestUri, PHP_URL_PATH);
        return is_string($path) && preg_match('~/ServiceCatalog(?:/|$)~', $path) === 1;
    }

    public static function categoryId(string $requestUri): ?int
    {
        if (!self::isCatalog($requestUri)) {
            return null;
        }

        $query = parse_url($requestUri, PHP_URL_QUERY);
        if (!is_string($query)) {
            return null;
        }

        parse_str($query, $parameters);
        $category = $parameters['category'] ?? null;
        if (!is_scalar($category) || !preg_match('/^[1-9]\d*$/D', (string) $category)) {
            return null;
        }

        return (int) $category;
    }
}
