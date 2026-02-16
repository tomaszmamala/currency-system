<?php

namespace App\Tests\Services;

use App\Entity\CurrencyAccount;
use App\Entity\BusinessPartner;
use App\Entity\CurrencyExchange;
use App\Entity\Transaction;
use App\Enums\CurrencyEnum;
use App\Enums\TransactionTypeEnum;
use App\Exceptions\TransactionExecutionException;
use App\Service\BalanceManager;
use App\Service\ExchangeManager;
use App\Service\ExchangeRateProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExchangeManagerTest extends TestCase
{
    private BalanceManager&MockObject $balanceManager;
    private EntityManagerInterface&MockObject $entityManager;
    private ExchangeRateProvider&MockObject $exchangeRateProvider;
    private ExchangeManager $exchangeManager;

    protected function setUp(): void
    {
        $this->balanceManager = $this->createMock(BalanceManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->exchangeRateProvider = $this->createMock(ExchangeRateProvider::class);

        $this->exchangeManager = new ExchangeManager(
            $this->balanceManager,
            $this->entityManager,
            $this->exchangeRateProvider
        );
    }

    public function testExecuteSuccessfully(): void
    {
        $businessPartner = new BusinessPartner();
        $businessPartner->setCountry('PL');

        $exchange = new CurrencyExchange();
        $exchange->setBusinessPartner($businessPartner);
        $exchange->setFromCurrency(CurrencyEnum::CHF);
        $exchange->setToCurrency(CurrencyEnum::EUR);
        $exchange->setFromAmount('100.00');
        $exchange->setDate(new \DateTimeImmutable());
        $exchange->setExecuted(false);

        $rate = '1.1';
        $expectedToAmount = '110.00';

        $this->exchangeRateProvider
            ->expects($this->once())
            ->method('getRate')
            ->with(CurrencyEnum::CHF, CurrencyEnum::EUR)
            ->willReturn($rate);

        $account = $this->createMock(CurrencyAccount::class);

        $this->balanceManager
            ->method('getOrCreateAccount')
            ->willReturn($account);

        $this->balanceManager
            ->expects($this->once())
            ->method('hasEnoughMoneyForPayout')
            ->with($account, '100.00')
            ->willReturn(true);

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->with($this->isInstanceOf(Transaction::class));

        $this->balanceManager
            ->expects($this->once())
            ->method('decreaseBalance')
            ->with($account, '100.00');

        $this->balanceManager
            ->expects($this->once())
            ->method('increaseBalance')
            ->with($account, $expectedToAmount);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->exchangeManager->execute($exchange);

        $this->assertTrue($exchange->isExecuted());
        $this->assertSame($rate, $exchange->getExchangeRate());
        $this->assertSame($expectedToAmount, $exchange->getToAmount());
        $this->assertInstanceOf(Transaction::class, $exchange->getSellTransaction());
        $this->assertInstanceOf(Transaction::class, $exchange->getBuyTransaction());
        $this->assertSame(TransactionTypeEnum::EXCHANGE_OUT, $exchange->getSellTransaction()->getType());
        $this->assertSame(TransactionTypeEnum::EXCHANGE_IN, $exchange->getBuyTransaction()->getType());
    }

    public function testExecuteThrowsExceptionWhenAlreadyExecuted(): void
    {
        $exchange = new CurrencyExchange();
        $exchange->setExecuted(true);

        $this->expectException(TransactionExecutionException::class);
        $this->expectExceptionMessage('Exchange is already executed');

        $this->exchangeManager->execute($exchange);
    }

    public function testExecuteThrowsExceptionWhenNoEnoughMoney(): void
    {
        $businessPartner = new BusinessPartner();
        $exchange = new CurrencyExchange();
        $exchange->setBusinessPartner($businessPartner);
        $exchange->setFromCurrency(CurrencyEnum::EUR);
        $exchange->setToCurrency(CurrencyEnum::USD);
        $exchange->setFromAmount('100.00');
        $exchange->setExecuted(false);

        $this->exchangeRateProvider
            ->method('getRate')
            ->willReturn('1.1');

        $account = $this->createMock(CurrencyAccount::class);

        $this->balanceManager
            ->method('getOrCreateAccount')
            ->willReturn($account);

        $this->balanceManager
            ->expects($this->once())
            ->method('hasEnoughMoneyForPayout')
            ->willReturn(false);

        $this->expectException(TransactionExecutionException::class);
        $this->expectExceptionMessage('You do not have enough money for this exchange');

        $this->exchangeManager->execute($exchange);
    }
}
