<?php

namespace App\Service;

use App\Entity\BusinessPartner;
use App\Entity\CurrencyAccount;
use App\Enums\CurrencyEnum;
use App\Repository\CurrencyAccountRepository;
use Doctrine\ORM\EntityManagerInterface;

class BalanceManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CurrencyAccountRepository $currencyAccountRepository
    ) {
    }

    public function getOrCreateAccount(BusinessPartner $businessPartner, CurrencyEnum $currency): CurrencyAccount
    {
        $account = $this->currencyAccountRepository->findByBusinessPartnerAndCurrency($businessPartner, $currency);

        if ($account === null) {
            $account = new CurrencyAccount();
            $account->setBusinessPartner($businessPartner);
            $account->setCurrency($currency);
            $account->setBalance('0');
            $this->entityManager->persist($account);
            $this->entityManager->flush();
        }

        return $account;
    }

    public function increaseBalance(CurrencyAccount $currencyAccount, string $amount): string
    {
        $balance = (float)$currencyAccount->getBalance();
        $balance += (float)$amount;
        $currencyAccount->setBalance((string)$balance);

        $this->entityManager->flush();

        return (string)$balance;
    }

    public function decreaseBalance(CurrencyAccount $currencyAccount, string $amount): string
    {
        $balance = (float)$currencyAccount->getBalance();
        $balance -= (float)$amount;
        $currencyAccount->setBalance((string)$balance);

        $this->entityManager->flush();

        return (string)$balance;
    }

    public function hasEnoughMoneyForPayout(CurrencyAccount $currencyAccount, string $amount): bool
    {
        $remainingBalance = (float)$currencyAccount->getBalance();
        $remainingBalance -= (float)$amount;

        return $remainingBalance >= 0;
    }
}
