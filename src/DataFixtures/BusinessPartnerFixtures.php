<?php

namespace App\DataFixtures;

use App\Entity\BusinessPartner;
use App\Entity\CurrencyAccount;
use App\Enums\BusinessPartnerStatusEnum;
use App\Enums\CurrencyEnum;
use App\Enums\LegalFormEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BusinessPartnerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $businessPartner = new BusinessPartner();
        $businessPartner->setName('AMNIS Treasury Services AG');
        $businessPartner->setStatus(BusinessPartnerStatusEnum::ACTIVE);
        $businessPartner->setLegalForm(LegalFormEnum::LIMITED_LIABILITY_COMPANY);
        $businessPartner->setAddress('Baslerstrasse 60');
        $businessPartner->setCity('Zürich');
        $businessPartner->setZip('8048');
        $businessPartner->setCountry('CH');

        $manager->persist($businessPartner);

        $chfAccount = new CurrencyAccount();
        $chfAccount->setBusinessPartner($businessPartner);
        $chfAccount->setCurrency(CurrencyEnum::CHF);
        $chfAccount->setBalance('0');
        $manager->persist($chfAccount);

        $eurAccount = new CurrencyAccount();
        $eurAccount->setBusinessPartner($businessPartner);
        $eurAccount->setCurrency(CurrencyEnum::EUR);
        $eurAccount->setBalance('0');
        $manager->persist($eurAccount);

        $manager->flush();
    }
}
