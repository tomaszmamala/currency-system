<?php

namespace App\Service;

use App\Entity\CurrencyExchange;
use App\Entity\Transaction;
use App\Enums\CurrencyEnum;
use App\Enums\TransactionTypeEnum;
use App\Exceptions\TransactionExecutionException;
use Doctrine\ORM\EntityManagerInterface;

readonly class ExchangeManager
{
    public function __construct(
        private BalanceManager         $balanceManager,
        private EntityManagerInterface $entityManager,
        private ExchangeRateProvider   $exchangeRateProvider
    ) {
    }

    public function execute(CurrencyExchange $exchange): void
    {
        if ($exchange->isExecuted()) {
            throw new TransactionExecutionException('Exchange is already executed');
        }

        $this->calculateRate($exchange);
        $this->validateBalance($exchange);
        $this->createTransactions($exchange);
        $this->updateBalances($exchange);

        $exchange->setExecuted(true);
        $this->entityManager->flush();
    }

    private function calculateRate(CurrencyExchange $exchange): void
    {
        $rate = $this->exchangeRateProvider->getRate($exchange->getFromCurrency(), $exchange->getToCurrency());
        $exchange->setExchangeRate($rate);

        $toAmount = sprintf('%.2f', (float) $exchange->getFromAmount() * (float) $rate);
        $exchange->setToAmount($toAmount);
    }

    private function validateBalance(CurrencyExchange $exchange): void
    {
        $fromAccount = $this->balanceManager->getOrCreateAccount(
            $exchange->getBusinessPartner(),
            $exchange->getFromCurrency()
        );

        if (!$this->balanceManager->hasEnoughMoneyForPayout($fromAccount, $exchange->getFromAmount())) {
            throw new TransactionExecutionException('You do not have enough money for this exchange');
        }
    }

    private function createTransactions(CurrencyExchange $exchange): void
    {
        $sellTransaction = $this->createTransaction(
            TransactionTypeEnum::EXCHANGE_OUT,
            $exchange->getFromCurrency(),
            $exchange->getFromAmount(),
            $exchange
        );

        $buyTransaction = $this->createTransaction(
            TransactionTypeEnum::EXCHANGE_IN,
            $exchange->getToCurrency(),
            $exchange->getToAmount(),
            $exchange
        );

        $exchange->setSellTransaction($sellTransaction);
        $exchange->setBuyTransaction($buyTransaction);
    }

    private function createTransaction(
        TransactionTypeEnum $type,
        CurrencyEnum $currency,
        string $amount,
        CurrencyExchange $exchange
    ): Transaction {
        $transaction = new Transaction();
        $transaction->setType($type);
        $transaction->setCurrency($currency);
        $transaction->setAmount($amount);
        $transaction->setBusinessPartner($exchange->getBusinessPartner());
        $transaction->setExecuted(true);
        $transaction->setDate($exchange->getDate());
        $transaction->setCountry($exchange->getBusinessPartner()->getCountry());
        $transaction->setIban('N/A');

        $label = $type === TransactionTypeEnum::EXCHANGE_OUT
            ? "Exchange {$exchange->getFromCurrency()->value} to {$exchange->getToCurrency()->value}"
            : "Exchange {$exchange->getToCurrency()->value} from {$exchange->getFromCurrency()->value}";
        $transaction->setName($label);

        $this->entityManager->persist($transaction);

        return $transaction;
    }

    private function updateBalances(CurrencyExchange $exchange): void
    {
        $fromAccount = $this->balanceManager->getOrCreateAccount(
            $exchange->getBusinessPartner(),
            $exchange->getFromCurrency()
        );

        $toAccount = $this->balanceManager->getOrCreateAccount(
            $exchange->getBusinessPartner(),
            $exchange->getToCurrency()
        );

        $this->balanceManager->decreaseBalance($fromAccount, $exchange->getFromAmount());
        $this->balanceManager->increaseBalance($toAccount, $exchange->getToAmount());
    }
}
