<?php

namespace App\Shared\Application\Command;

use App\Shared\Domain\Bus\AsyncMessageInterface;

class RetryTransactionalQueueCommand implements AsyncMessageInterface
{
}
