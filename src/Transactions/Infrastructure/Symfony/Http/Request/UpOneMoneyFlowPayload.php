<?php

namespace App\Transactions\Infrastructure\Symfony\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UpOneMoneyFlowPayload
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $from,
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $to,
        #[Assert\NotBlank]
        public string $organizationCif,
        #[Assert\NotBlank]
        public string $accountName,
    ) {
    }

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        $fromDate = \DateTime::createFromFormat('Y-m-d', $this->from);
        $toDate = \DateTime::createFromFormat('Y-m-d', $this->to);
        $today = new \DateTime();

        if ($fromDate > $today) {
            $context->buildViolation('The "from" date cannot be later than today.')
                ->atPath('from')
                ->addViolation();
        }

        if ($toDate > $today) {
            $context->buildViolation('The "to" date cannot be later than today.')
                ->atPath('to')
                ->addViolation();
        }

        if ($fromDate && $toDate) {
            if ($fromDate > $toDate) {
                $context->buildViolation('The "from" date cannot be later than the "to" date.')
                    ->atPath('from')
                    ->addViolation();
            }

            $interval = $fromDate->diff($toDate);
            if ($interval->m > 6 || $interval->y > 0) {
                $context->buildViolation('The interval between "from" and "to" cannot exceed 6 months.')
                    ->atPath('to')
                    ->addViolation();
            }
        }
    }
}
