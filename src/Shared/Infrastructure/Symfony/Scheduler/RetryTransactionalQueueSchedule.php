<?php

namespace App\Shared\Infrastructure\Symfony\Scheduler;

use App\Shared\Application\Command\RetryTransactionalQueueCommand;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('failure_messages_queue')]
class RetryTransactionalQueueSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private LockFactory $lockFactory,
        #[Autowire(env: 'APP_ENV')]
        private readonly string $environment,
    ) {
    }

    public function getSchedule(): Schedule
    {
        $schedule = new Schedule();

        $this->retryDqlMessages($schedule);

        return $schedule->lock(
            $this->lockFactory->createLock('retry_transactional_messages_schedule_'.$this->environment, 60)
        );
    }

    private function retryDqlMessages(Schedule $schedule): void
    {
        $schedule->add(RecurringMessage::cron('@daily', new RetryTransactionalQueueCommand()));
    }
}
