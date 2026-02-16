<?php

namespace App\Repository;

use App\Entity\BusinessPartner;
use App\Entity\CurrencyAccount;
use App\Enums\CurrencyEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CurrencyAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrencyAccount::class);
    }

    public function findByBusinessPartnerAndCurrency(BusinessPartner $businessPartner, CurrencyEnum $currency): ?CurrencyAccount
    {
        return $this->createQueryBuilder('ca')
            ->andWhere('ca.businessPartner = :businessPartner')
            ->andWhere('ca.currency = :currency')
            ->setParameter('businessPartner', $businessPartner)
            ->setParameter('currency', $currency)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
