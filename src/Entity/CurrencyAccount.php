<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Enums\CurrencyEnum;
use App\Repository\CurrencyAccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CurrencyAccountRepository::class)]
#[ORM\Table(name: 'currency_accounts')]
#[ORM\UniqueConstraint(name: 'unique_bp_currency', columns: ['business_partner_id', 'currency'])]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['CurrencyAccountView']]
)]
#[ApiFilter(SearchFilter::class, properties: ['businessPartner' => 'exact'])]
class CurrencyAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['CurrencyAccountView', 'BusinessPartnerView'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: BusinessPartner::class, inversedBy: 'currencyAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['CurrencyAccountView'])]
    private BusinessPartner $businessPartner;

    #[ORM\Column(type: Types::STRING, length: 3, enumType: CurrencyEnum::class)]
    #[Groups(['CurrencyAccountView', 'BusinessPartnerView'])]
    private CurrencyEnum $currency;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['CurrencyAccountView', 'BusinessPartnerView'])]
    private string $balance = '0';

    public function getId(): int
    {
        return $this->id;
    }

    public function getBusinessPartner(): BusinessPartner
    {
        return $this->businessPartner;
    }

    public function setBusinessPartner(BusinessPartner $businessPartner): void
    {
        $this->businessPartner = $businessPartner;
    }

    public function getCurrency(): CurrencyEnum
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyEnum $currency): void
    {
        $this->currency = $currency;
    }

    public function getBalance(): string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): void
    {
        $this->balance = $balance;
    }
}
