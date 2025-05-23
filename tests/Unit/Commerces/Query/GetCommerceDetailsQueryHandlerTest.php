<?php

namespace App\Tests\Unit\Commerces\Query;

use App\Commerces\Application\Query\GetCommerceDetailsQuery;
use App\Commerces\Application\Query\GetCommerceDetailsQueryHandler;
use App\Commerces\Domain\Model\Commerce;
use App\Commerces\Infrastructure\Persistance\FucRepositoryInterface;
use App\Commerces\Infrastructure\Symfony\Model\Response\CommerceDetailsSchema;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;
use PHPUnit\Framework\TestCase;

class GetCommerceDetailsQueryHandlerTest extends TestCase
{
    public function testHandleReturnsCachedCommerce(): void
    {
        // Valores reales para el objeto Commerce
        $commerceId = 'commerce-id-1';
        $commerceData = [
            'code' => '123',
            'name' => 'Test Commerce',
            'csb' => 'CSB123',
            'cif_nif' => 'A12345678',
            'area' => 'Central',
            'province' => 'Madrid',
            'address' => '123 Main St',
            'sector_int' => 'Retail',
            'sector_act' => 'Food',
        ];
        $cachedCommerce = new Commerce($commerceData);

        $mockCache = $this->createMock(CacheServiceInterface::class);
        $mockCache->expects($this->once())
            ->method('keyExist')
            ->with($commerceId)
            ->willReturn(true);

        $mockCache->expects($this->once())
            ->method('getFromStore')
            ->with($commerceId, Commerce::class)
            ->willReturn($cachedCommerce);

        $mockRepository = $this->createMock(FucRepositoryInterface::class);
        $mockRepository->expects($this->never())
            ->method('findByCommerceId');

        $handler = new GetCommerceDetailsQueryHandler($mockRepository, $mockCache);

        $query = new GetCommerceDetailsQuery($commerceId);

        $result = $handler->__invoke($query);

        $this->assertInstanceOf(CommerceDetailsSchema::class, $result);
    }

    public function testHandleFetchesAndCachesCommerce(): void
    {
        // Valores reales para el objeto Commerce
        $commerceId = 'commerce-id-2';
        $commerceData = [
            'code' => '123',
            'name' => 'Test Commerce',
            'csb' => 'CSB123',
            'cif_nif' => 'A12345678',
            'area' => 'Central',
            'province' => 'Madrid',
            'address' => '123 Main St',
            'sector_int' => 'Retail',
            'sector_act' => 'Food',
        ];
        $cachedCommerce = new Commerce($commerceData);

        $mockCache = $this->createMock(CacheServiceInterface::class);
        $mockCache->expects($this->once())
            ->method('keyExist')
            ->with($commerceId)
            ->willReturn(false);

        $mockCache->expects($this->once())
            ->method('store')
            ->with($commerceId, $cachedCommerce, $mockCache->getDefaultTtl(), Commerce::class);

        $mockRepository = $this->createMock(FucRepositoryInterface::class);
        $mockRepository->expects($this->once())
            ->method('findByCommerceId')
            ->with($commerceId)
            ->willReturn($cachedCommerce);

        $handler = new GetCommerceDetailsQueryHandler($mockRepository, $mockCache);

        $query = new GetCommerceDetailsQuery($commerceId);

        $result = $handler->__invoke($query);

        $this->assertInstanceOf(CommerceDetailsSchema::class, $result);
    }
}
