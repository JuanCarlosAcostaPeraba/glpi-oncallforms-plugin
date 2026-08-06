<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms\Tests\Unit;

use GlpiPlugin\Oncallforms\ServiceCatalogRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServiceCatalogRequestTest extends TestCase
{
    public function testExtractsCategoryFromCatalogUrl(): void
    {
        self::assertTrue(ServiceCatalogRequest::isCatalog('/ServiceCatalog'));
        self::assertSame(27, ServiceCatalogRequest::categoryId('/ServiceCatalog?category=27'));
        self::assertSame(27, ServiceCatalogRequest::categoryId('/glpi/ServiceCatalog?foo=bar&category=27'));
    }

    #[DataProvider('unrelatedRequests')]
    public function testRejectsUnrelatedOrInvalidRequests(string $requestUri): void
    {
        self::assertNull(ServiceCatalogRequest::categoryId($requestUri));
    }

    public function testRejectsNonCatalogPath(): void
    {
        self::assertFalse(ServiceCatalogRequest::isCatalog('/Form/Render/27'));
    }

    /** @return iterable<string, array{string}> */
    public static function unrelatedRequests(): iterable
    {
        yield 'catalog root' => ['/ServiceCatalog'];
        yield 'other page' => ['/Form/Render/27?category=27'];
        yield 'zero category' => ['/ServiceCatalog?category=0'];
        yield 'injected category' => ['/ServiceCatalog?category=27%20OR%201%3D1'];
        yield 'array category' => ['/ServiceCatalog?category[]=27'];
    }
}
