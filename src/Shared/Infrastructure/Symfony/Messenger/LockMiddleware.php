<?php

namespace App\Shared\Infrastructure\Symfony\Messenger;

use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class LockMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LockFactory $lockFactory,
        private LoggerInterface $logger
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        if (!$message instanceof LockableMessageInterface) {
            return $stack->next()->handle($envelope, $stack);
        }

        $this->logger->info('Acquiring lock for message '.$message->getLockKey());
        $lock = $this->lockFactory->createLock($message->getLockKey(), 60);
        $lock->acquire(true);
        try {
            return $stack->next()->handle($envelope, $stack);
        } finally {
            $lock->release();
        }
    }
}
