<?php

namespace App\Tests\Unit\Transactions\Enricher;

use App\Commerces\Domain\Model\Commerce;
use App\Commerces\Infrastructure\Persistance\FucRepositoryInterface;
use App\Settings\Application\Services\CheckSettingServiceInterface;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;
use App\Transactions\Application\Command\Enricher\CommerceTransactionEnricher;
use App\Transactions\Domain\Model\MslTransaction;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CommerceTransactionEnricherTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testEnrichWithCommerceDetails(): void
    {
        $cache = $this->createMock(CacheServiceInterface::class);
        $repository = $this->createMock(FucRepositoryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $check = $this->createMock(CheckSettingServiceInterface::class);

        $check->method('isTransactionsCommerceEnrichmentEnabled')->willReturn(true);

        $commerce = new Commerce([
            'code' => 'COM123',
            'name' => 'Commerce Name',
            'csb' => 'CSB001',
            'cif_nif' => 'CIF123456',
            'area' => 'Area 51',
            'province' => 'Some Province',
            'address' => '123 Commerce St',
            'sector_int' => 'Retail',
            'sector_act' => 'Electronics',
        ]);

        $transaction = $this->getMockBuilder(MslTransaction::class)
            ->onlyMethods(['getAcceptorId', 'enrichWithCommerceDetails'])
            ->disableOriginalConstructor()
            ->getMock();

        $transaction->method('getAcceptorId')->willReturn('acceptor123');

        $cache->method('keyExist')->with('commerce-details-acceptor123')->willReturn(false);
        $cache->method('getFromStore')->willReturn(null);

        $repository->method('findByCommerceId')->with('acceptor123')->willReturn($commerce);

        $transaction->expects($this->once())
            ->method('enrichWithCommerceDetails')
            ->with(
                'COM123',          // code
                'Commerce Name',   // name
                'CSB001',          // csb
                'CIF123456',       // cif_nif
                'Area 51',         // area
                'Some Province',   // province
                '123 Commerce St', // address
                'Retail',          // sector_int
                'Electronics'      // sector_act
            )->willReturnSelf();

        $enricher = new CommerceTransactionEnricher($repository, $cache, $logger, $check);

        $result = $enricher->enrich($transaction);

        $this->assertSame($transaction, $result);
    }
}
