<?php

namespace App\Tests\Unit\Transactions;

use App\Shared\Domain\Bus\DispatcherInterface;
use App\Transactions\Application\Command\SaveUpOneTransactionCommand;
use App\Transactions\Application\Event\UpOneTransactionRaisedEventHandler;
use App\Transactions\Domain\Event\UpOneTransactionRaisedEvent;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UpOneTransactionRaisedEventHandling extends TestCase
{
    private UpOneTransactionRaisedEventHandler $handler;
    private MockObject $dispatcher;
    private MockObject $logger;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(DispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->handler = new UpOneTransactionRaisedEventHandler($this->dispatcher, $this->logger);
    }

    public function testHandleDispatchesSaveUpOneTransactionCommand(): void
    {
        $data = [
            'transaction_id' => 'e7a8891460f4402ca121b747739b25fb',
            'datetime' => '2025-02-19T10:12:32+01:00',
            'account_id' => '843578020000263973',
            'account_name' => '001-Comida',
            'product_id' => [
                'value' => '01939238-a72b-71a4-a4c2-923765ec1ca3',
            ],
            'product_name' => 'General',
            'product_code' => 'UP0201A28203537LWCRETSCHMAR001',
            'cif' => 'B90572694',
            'as400' => '12715',
            'ceco' => null,
            'financial_impact_type' => 'credit',
            'amount' => 1000,
            'customer_id' => '943578020000378118',
            'customer_name' => 'VICTOR',
            'customer_surname' => 'ARANDA MALDONADO',
            'organization_id' => '0191eb0a-6e21-74fe-9109-b987db496cd2',
            'organization_name' => 'L.W. Cretschmar Española, S.A.',
            'notification_type' => 'account_credit',
        ];

        $type = 'account_credit';

        $event = new UpOneTransactionRaisedEvent($data, $type);
        $transaction = $event->castUpOneTransaction();

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(new SaveUpOneTransactionCommand($transaction));

        $this->logger->expects($this->once())
            ->method('info')
            ->with('UpOne transaction raised and sent to persistance', [
                'transactionId' => $transaction->getTransactionId(),
                'accountId' => $transaction->getAccountId(),
            ]);

        ($this->handler)($event);
    }
}
