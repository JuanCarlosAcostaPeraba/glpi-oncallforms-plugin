<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms;

final class ServiceCatalogRequest
{
    public static function categoryId(string $requestUri): ?int
    {
        $path = parse_url($requestUri, PHP_URL_PATH);
        if (!is_string($path) || preg_match('~/ServiceCatalog(?:/|$)~', $path) !== 1) {
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
