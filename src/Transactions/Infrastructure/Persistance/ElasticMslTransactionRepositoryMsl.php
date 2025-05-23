<?php

namespace App\Transactions\Infrastructure\Persistance;

use App\Shared\Domain\Contracts\UidGeneratorInterface;
use App\Shared\Http\Pagination\Page;
use App\Shared\Infrastructure\Elastica\ElasticaClientInterface;
use App\Transactions\Application\Query\ListMslTransactionsQueryFilters;
use App\Transactions\Domain\Model\MslTransaction;

class ElasticMslTransactionRepositoryMsl implements MslTransactionsRepositoryInterface
{
    public function __construct(
        private ElasticaClientInterface $elasticaClient,
        private UidGeneratorInterface $uuidGenerator,
        private readonly string $mslTransactionsIndex,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function save(MslTransaction $transaction): void
    {
        $this->elasticaClient->addRegistry(
            $this->mslTransactionsIndex,
            $transaction,
            $transaction->getTransactionId().'_'.$this->uuidGenerator->generate()->value(),
            $transaction->getTime()
        );
    }

    /**
     * @throws \Exception
     */
    public function findByTransactionId(
        string $transactionId,
        \DateTime $from,
        \DateTime $to,
        array $criteria = []
    ): ?MslTransaction {
        $result = $this->elasticaClient->criteriaSearch(
            $this->mslTransactionsIndex,
            'transaction_id',
            $transactionId,
            $from,
            $to,
            $criteria
        );

        if (0 === count($result)) {
            return null;
        } else {
            return new MslTransaction($result[0]);
        }
    }

    /**
     * @return MslTransaction[]
     *
     * @throws \Exception
     */
    public function findByAccountId(string $accountId, \DateTime $from, \DateTime $to, array $criteria = []): array
    {
        $result = $this->elasticaClient->criteriaSearch(
            $this->mslTransactionsIndex,
            'account',
            $accountId,
            $from,
            $to,
            $criteria
        );

        return array_map(static function ($item) {
            return new MslTransaction($item);
        }, $result);
    }

    /**
     * @throws \Exception
     */
    public function findFistPreviousFromByAccountId(
        string $accountId,
        \DateTime $from,
    ): ?MslTransaction {
        $query = 'from '.$this->mslTransactionsIndex.' | where account == ? | where datetime < to_datetime(?) | where ((card_trantype == "POS" or card_trantype == "ECOM") and message_type == "1240") or (card_trantype == "API") | sort datetime desc, ledger_balance asc | limit 1';

        $params = [
            $accountId,
            $from->format('Y-m-d').'T00:00:00',
        ];

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        if (empty($result[0])) {
            return null;
        } else {
            return new MslTransaction($result[0]);
        }
    }

    /**
     * @throws \Exception
     */
    public function findInitialBalanceByAccountId(
        string $accountId,
        \DateTime $from,
    ): float {
        $query = 'from '.$this->mslTransactionsIndex.' | where account == ? | where datetime < to_datetime(?) | where ((card_trantype == "POS" or card_trantype == "ECOM") and message_type == "1240") or (card_trantype == "API") | sort datetime desc, ledger_balance asc | limit 1 | keep ledger_balance';

        $params = [
            $accountId,
            $from->format('Y-m-d').'T00:00:00',
        ];

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        return $result[0]['ledger_balance'] ?? 0.0;
    }

    /**
     * @throws \Exception
     */
    public function findLastTransactionByAccountId(
        string $accountId,
        \DateTime $from,
        \DateTime $to
    ): ?MslTransaction {
        $query = 'from '.$this->mslTransactionsIndex.' | where account == ? | where datetime > to_datetime(?) and datetime < to_datetime(?) | where ((card_trantype == "POS" or card_trantype == "ECOM") and message_type == "1240") or (card_trantype == "API") | sort datetime desc, ledger_balance asc | limit 1';

        $params = [
            $accountId,
            $from->format('Y-m-d').'T00:00:00',
            $to->format('Y-m-d').'T23:59:59',
        ];

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        if (empty($result[0])) {
            return null;
        } else {
            return new MslTransaction($result[0]);
        }
    }

    /**
     * @throws \Exception
     */
    public function findLastBalanceByAccountId(
        string $accountId,
        \DateTime $from,
        \DateTime $to
    ): float {
        $query = 'from '.$this->mslTransactionsIndex.' | where account == ? | where datetime > to_datetime(?) and datetime < to_datetime(?) | where ((card_trantype == "POS" or card_trantype == "ECOM") and message_type == "1240") or (card_trantype == "API") | sort datetime desc, ledger_balance asc | limit 1 | keep ledger_balance';

        $params = [
            $accountId,
            $from->format('Y-m-d').'T00:00:00',
            $to->format('Y-m-d').'T23:59:59',
        ];

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        return $result[0]['ledger_balance'] ?? 0.0;
    }

    public function getConsumedByAccountId(string $accountId, \DateTime $from, \DateTime $to): float
    {
        $query = 'from '.$this->mslTransactionsIndex.' | where account == ? | where datetime > to_datetime(?) and datetime < to_datetime(?) | where (card_trantype == "POS" or card_trantype == "ECOM") and message_type == "1240" and financial_impact_type == "DR" | sort datetime desc, ledger_balance asc | stats paid = sum(billing_amount)';

        $params = [
            $accountId,
            $from->format('Y-m-d').'T00:00:00',
            $to->format('Y-m-d').'T23:59:59',
        ];

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        if (!array_key_exists('paid', $result[0])) {
            return 0.0;
        }

        return $result[0]['paid'] ?? 0.0;
    }

    public function getConsumptionsByAccountIds(array $accountIds, \DateTime $from, \DateTime $to): array
    {
        $formatedAccounts = '';

        for ($i = 0; $i < count($accountIds); ++$i) {
            $formatedAccounts .= '?';
            if ($i < count($accountIds) - 1) {
                $formatedAccounts .= ',';
            }
        }

        $query = 'from '.$this->mslTransactionsIndex.'
    | where account in ('.$formatedAccounts.')
    | where datetime > to_datetime(?) and datetime < to_datetime(?)
    | sort datetime desc, ledger_balance asc 
    | eval pos_or_ecom_1240 = (card_trantype == "POS" or card_trantype == "ECOM" ) and message_type == "1240" and financial_impact_type == "DR"
    | eval pos_or_ecom_1240_return = (card_trantype == "POS" or card_trantype == "ECOM") and message_type == "1240" and financial_impact_type == "CR"
    | eval api_cr = card_trantype == "API" and financial_impact_type == "CR" and response_code == "00"
    | eval api_dr = card_trantype == "API" and financial_impact_type == "DR" and response_code == "00"
    | eval pos = card_trantype == "POS" and message_type == "1240"
    | eval ecom = card_trantype == "ECOM" and message_type == "1240"
    | eval api = card_trantype == "API"
    | stats 
        total_pos = count(case(pos, 1, null)),
        total_ecom = count(case(ecom, 1, null)),
        total_api = count(case(api, 1, null)),
        returned = sum(case(pos_or_ecom_1240_return, billing_amount, null)),
        consumed_pos_ecom = sum(case(pos_or_ecom_1240, billing_amount, null)),
        recharged = sum(case(api_cr, billing_amount, null)),
        discharged = sum(case(api_dr, billing_amount, null))
        by account';

        $params = [];

        foreach ($accountIds as $accountId) {
            $params[] = $accountId;
        }

        $params[] = $from->format('Y-m-d').'T00:00:00';
        $params[] = $to->format('Y-m-d').'T23:59:59';

        return $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );
    }

    public function getRechargedAndDischargedByAccountIds(array $accountIds, \DateTime $from, \DateTime $to): array
    {
        $formatedAccounts = '';

        for ($i = 0; $i < count($accountIds); ++$i) {
            $formatedAccounts .= 'account == ? ';
            if ($i < count($accountIds) - 1) {
                $formatedAccounts .= 'or ';
            }
        }

        $query = 'from '.$this->mslTransactionsIndex.'
    | where '.$formatedAccounts.'
    | where datetime > to_datetime(?) and datetime < to_datetime(?)
    | eval api_cr = card_trantype == "API" and financial_impact_type == "CR" and response_code == "00"
    | eval api_dr = card_trantype == "API" and financial_impact_type == "DR" and response_code == "00"
    | stats 
        recharged = sum(case(api_cr, billing_amount, null)),
        discharged = sum(case(api_dr, billing_amount, null))';

        $params = [];

        foreach ($accountIds as $accountId) {
            $params[] = $accountId;
        }

        $params[] = $from->format('Y-m-d').'T00:00:00';
        $params[] = $to->format('Y-m-d').'T23:59:59';

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        return [
            'recharged' => $result[0]['recharged'] ?? 0.0,
            'discharged' => $result[0]['discharged'] ?? 0.0,
        ];
    }

    public function getRechargedByAccountId(string $accountId, \DateTime $from, \DateTime $to): float
    {
        $query = 'from '.$this->mslTransactionsIndex.' | where account == ? | where datetime > to_datetime(?) and datetime < to_datetime(?) | where card_trantype == "API" and financial_impact_type == "CR" and response_code == "00" | sort datetime desc, ledger_balance asc | stats recharged = sum(billing_amount)';

        $params = [
            $accountId,
            $from->format('Y-m-d').'T00:00:00',
            $to->format('Y-m-d').'T23:59:59',
        ];

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        if (!array_key_exists('recharged', $result)) {
            return 0.0;
        }

        return $result['recharged'] ?? 0.0;
    }

    public function getDischargedByAccountId(string $accountId, \DateTime $from, \DateTime $to): float
    {
        $query = 'from '.$this->mslTransactionsIndex.' | where account == ? | where datetime > to_datetime(?) and datetime < to_datetime(?) | where card_trantype == "API" and financial_impact_type == "DR" and response_code == "00" | sort datetime desc, ledger_balance asc | stats discharged = sum(billing_amount)';

        $params = [
            $accountId,
            $from->format('Y-m-d').'T00:00:00',
            $to->format('Y-m-d').'T23:59:59',
        ];

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        if (!array_key_exists('discharged', $result)) {
            return 0.0;
        }

        return $result['discharged'] ?? 0.0;
    }

    public function countApiTransactionsByAccountId(string $accountId, \DateTime $from, \DateTime $to): int
    {
        $query = 'from '.$this->mslTransactionsIndex.' | where account == ? | where datetime > to_datetime(?) and datetime < to_datetime(?) | where card_trantype == "API" and response_code == "00" | stats count = count(account)';

        $params = [
            $accountId,
            $from->format('Y-m-d').'T00:00:00',
            $to->format('Y-m-d').'T23:59:59',
        ];

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        if (!array_key_exists('count', $result)) {
            return 0;
        }

        return $result['count'];
    }

    public function countEcomTransactionsByAccountId(string $accountId, \DateTime $from, \DateTime $to): int
    {
        $query = 'from '.$this->mslTransactionsIndex.' | where account == ? | where datetime > to_datetime(?) and datetime < to_datetime(?) | where card_trantype == "POS" and message_type == "1240" | stats count = count(account)';

        $params = [
            $accountId,
            $from->format('Y-m-d').'T00:00:00',
            $to->format('Y-m-d').'T23:59:59',
        ];

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        if (!array_key_exists('count', $result)) {
            return 0;
        }

        return $result['count'];
    }

    public function countPosTransactionsByAccountId(string $accountId, \DateTime $from, \DateTime $to): int
    {
        $query = 'from '.$this->mslTransactionsIndex.' | where account == ? | where datetime > to_datetime(?) and datetime < to_datetime(?) | where card_trantype == "ECOM" and message_type == "1240" | stats count = count(account)';

        $params = [
            $accountId,
            $from->format('Y-m-d').'T00:00:00',
            $to->format('Y-m-d').'T23:59:59',
        ];

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        if (!array_key_exists('count', $result)) {
            return 0;
        }

        return $result['count'];
    }

    public function getConsumptionsByAccountId(string $accountId, \DateTime $from, \DateTime $to): array
    {
        $query = 'from '.$this->mslTransactionsIndex.' | where account == ? | where datetime > to_datetime(?) and datetime < to_datetime(?) | sort datetime desc, ledger_balance asc | limit 10000
    | eval pos_or_ecom_1240 = (card_trantype == "POS" or card_trantype == "ECOM" ) and message_type == "1240"
    | eval api_cr = card_trantype == "API" and financial_impact_type == "CR" and response_code == "00"
    | eval api_dr = card_trantype == "API" and financial_impact_type == "DR" and response_code == "00"
    | eval pos = card_trantype == "POS" and message_type == "1240"
    | eval ecom = card_trantype == "ECOM" and message_type == "1240"
    | eval api = card_trantype == "API"
    | stats 
        total_pos = count(case(pos, 1, null)),
        total_ecom = count(case(ecom, 1, null)),
        total_api = count(case(api, 1, null)),
        consumed_pos_ecom = sum(case(pos_or_ecom_1240, billing_amount, null)),
        recharged = sum(case(api_cr, billing_amount, null)),
        discharged = sum(case(api_dr, billing_amount, null))';

        $params = [
            $accountId,
            $from->format('Y-m-d').'T00:00:00',
            $to->format('Y-m-d').'T23:59:59',
        ];

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        return [
            'totalPostTransactions' => $result['total_pos'] ?? 0,
            'totalEcomTransactions' => $result['total_ecom'] ?? 0,
            'totalApiTransactions' => $result['total_api'] ?? 0,
            'consumed' => $result['consumed_pos_ecom'] ?? 0,
            'rechargeSum' => $result['recharged'] ?? 0,
            'dischargeSum' => $result['discharged'] ?? 0,
        ];
    }

    /**
     * @return MslTransaction[]
     *
     * @throws \Exception
     */
    public function findByCardId(string $cardId, \DateTime $from, \DateTime $to, array $criteria = []): array
    {
        $result = $this->elasticaClient->criteriaSearch(
            $this->mslTransactionsIndex,
            'card_id',
            $cardId,
            $from,
            $to,
            $criteria
        );

        return array_map(static function ($item) {
            return new MslTransaction($item);
        }, $result);
    }

    /**
     * @throws \Exception
     */
    public function findByFilters(ListMslTransactionsQueryFilters $filters, Page $page): array
    {
        $criteria = $this->buildCriteria($filters);

        return array_map(static function (array $transactionData) {
            return new MslTransaction($transactionData);
        },
            $this->elasticaClient->findBy(
                $this->mslTransactionsIndex,
                $filters->getFrom(),
                $filters->getTo(),
                $criteria,
                $page->pageNumber(),
                $page->limit()
            ));
    }

    /**
     * @throws \Exception
     */
    public function countByFilters(ListMslTransactionsQueryFilters $filters, Page $page): int
    {
        $criteria = $this->buildCriteria($filters);

        return $this->elasticaClient->findByCount(
            $this->mslTransactionsIndex,
            $filters->getFrom(),
            $filters->getTo(),
            $criteria
        );
    }

    private function buildCriteria(ListMslTransactionsQueryFilters $filters): array
    {
        $criteria = [];

        // <----------Single match filters-->
        if ($filters->getTransactionType()) {
            $criteria['card_trantype'] = $filters->getTransactionType();
        }

        if ($filters->getHoldFlag()) {
            $criteria['holdflag'] = $filters->getHoldFlag();
        }

        if ($filters->getAccountName()) {
            $criteria['account_name'] = $filters->getAccountName();
        }

        if ($filters->getFinancialImpact()) {
            $criteria['financial_impact_type'] = $filters->getFinancialImpact();
        }
        // <----------Single match filters-->

        // <----------Multi match filters-->
        if ($filters->getCardIds()) {
            $criteria['card_ids'] = $filters->getCardIds();
        }

        if ($filters->getAccountIds()) {
            $criteria['account_ids'] = $filters->getAccountIds();
        }
        // <----------Multi match filters-->

        return $criteria;
    }

    public function getCardIdFromAccountId(string $accountId): string
    {
        $query = 'from '.$this->mslTransactionsIndex.' | where account == ? | limit 1 | keep card_id';

        $params = [
            $accountId,
        ];

        $result = $this->elasticaClient->executeEsQlQuery(
            $query,
            $params
        );

        return $result[0]['card_id'] ?? '';
    }
}
