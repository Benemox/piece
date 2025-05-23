<?php

namespace App\Transactions\Application\Query;

use App\Shared\Domain\Bus\HandlerInterface;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;
use App\Transactions\Infrastructure\Persistance\MslTransactionsRepositoryInterface;

class GetMslAccountsMoneyFlowQueryHandler implements HandlerInterface
{
    public function __construct(
        private MslTransactionsRepositoryInterface $mslTransactionsRepository,
        private CacheServiceInterface $cache
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(GetMslAccountsMoneyFlowQuery $query): array
    {
        $result = [];

        foreach ($query->accountIdsGroups as $group) {
            if (!array_key_exists('id', $group)) {
                continue;
            }
            if (!array_key_exists('accounts', $group)) {
                continue;
            }

            $key = 'accounts-money-flow-'.$group['id'].':'.$query->from->format(
                'Y-m-d'
            ).':'.$query->to->format('Y-m-d').':'.md5(serialize($group['accounts']));

            if ($this->cache->keyExist($key)) {
                $result[$group['id']] = $this->cache->getFromStore($key, 'array');
                continue;
            }

            [
                'recharged' => $recharge,
                'discharged' => $discharge,
            ] = $this->mslTransactionsRepository->getRechargedAndDischargedByAccountIds(
                $group['accounts'],
                $query->from,
                $query->to
            );
            $data = [
                'recharge' => round($recharge, 3),
                'discharge' => round($discharge, 3),
                'difference' => round($recharge - $discharge, 2),
            ];

            $this->cache->store($key, $data, $this->cache->getDefaultTtl() * 24, 'array');

            $result[$group['id']] = $data;
        }

        return $result;
    }
}
