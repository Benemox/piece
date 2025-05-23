<?php

namespace App\Tests\Unit\Transactions;

use App\Transactions\Application\Command\MslTransactionEnricherService;
use App\Transactions\Application\Command\TransactionEnricherInterface;
use App\Transactions\Domain\Model\MslTransaction;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class MslTransactionEnricherServiceTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testEnrichCallsAllEnrichers(): void
    {
        $enricher1 = $this->createMock(TransactionEnricherInterface::class);
        $enricher2 = $this->createMock(TransactionEnricherInterface::class);

        $transaction = $this->createMock(MslTransaction::class);

        $enricher1->expects($this->once())
            ->method('enrich')
            ->with($transaction)
            ->willReturn($transaction);

        $enricher2->expects($this->once())
            ->method('enrich')
            ->with($transaction)
            ->willReturn($transaction);

        $service = new MslTransactionEnricherService([$enricher1, $enricher2]);

        $result = $service->enrich($transaction);

        $this->assertInstanceOf(MslTransaction::class, $result);
    }
}
