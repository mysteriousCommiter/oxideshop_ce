<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Price;

use DateTime;
use oxcmp_user;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Object2Group;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\Voucher;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Application\Model\VoucherSerie;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\ShopIdCalculator;
use OxidTestCase;

final class VouchersTest extends OxidTestCase
{
    private const FIRST_VOUCHER_ID = 'testId1';
    private const SECOND_VOUCHER_ID = 'testId2';
    private const VOUCHER_SERIES_ID = 'testSeries';
    private const FIRST_ARTICLE = '1951'; // price: 14.0
    private const SECOND_ARTICLE = '1952'; // price: 6.0
    private const GROUP_ID = 'oxidnewcustomer';

    protected function setUp(): void
    {
        $this->createUser();

        $this->createVoucherSeries();

        $this->createFistVoucher();
        $this->addVoucherSeriesToUserGroup();

        $this->createSecondVoucher();

        $this->addVoucherToSpecificProduct(self::FIRST_VOUCHER_ID,self::FIRST_ARTICLE);
        $this->addVoucherToSpecificProduct(self::SECOND_VOUCHER_ID,self::FIRST_ARTICLE);
        $this->addVoucherToSpecificProduct(self::FIRST_VOUCHER_ID,self::SECOND_ARTICLE);
        $this->addVoucherToSpecificProduct(self::SECOND_VOUCHER_ID,self::SECOND_ARTICLE);
    }

    protected function tearDown(): void
    {
        $this->cleanUpTable("oxvouchers");
        $this->cleanUpTable("oxvoucherseries");
        $this->cleanUpTable("oxobject2discount");
        $this->cleanUpTable("oxobject2group");
        parent::tearDown();
    }

    public function testVouchersForSpecificCategoriesAndProducts(): void
    {
        $this->checkVoucherWithoutLogin();
        $basket = $this->checkVoucherWithUserLogin();
        $basket = $this->checkIfVoucherCalculateOnce($basket);
        $this->checkMultipleVouchersInSameUserGroup($basket);
    }

    private function createVoucherSeries(): void
    {
        $startDate = (new DateTime())->modify('-1 day')->format('Y-m-d 00:00:00');
        $endDate = (new DateTime())->modify('+1 day')->format('Y-m-d 00:00:00');

        $voucherSeries = oxNew(VoucherSerie::class);
        $voucherSeries->setId(self::VOUCHER_SERIES_ID);
        $voucherSeries->oxvoucherseries__oxshopid = new Field('1');
        $voucherSeries->oxvoucherseries__oxdiscount = new Field(5);
        $voucherSeries->oxvoucherseries__oxdiscounttype = new Field('absolute');
        $voucherSeries->oxvoucherseries__oxbegindate = new Field($startDate);
        $voucherSeries->oxvoucherseries__oxenddate = new Field($endDate);
        $voucherSeries->oxvoucherseries__oxallowsameseries = new Field('1');
        $voucherSeries->oxvoucherseries__oxalowotherseries = new Field('0');
        $voucherSeries->oxvoucherseries__oxallowuseanother = new Field('1');
        $voucherSeries->oxvoucherseries__oxminimumvalue = new Field('0.00');
        $voucherSeries->oxvoucherseries__oxcalculateonce = new Field('1');
        $voucherSeries->save();
    }
    private function createFistVoucher(): void
    {
        $voucher = oxNew(Voucher::class);
        $voucher->setId(self::FIRST_VOUCHER_ID);
        $voucher->oxvouchers__oxvouchernr = new Field(self::FIRST_VOUCHER_ID);
        $voucher->oxvouchers__oxvoucherserieid = new Field(self::VOUCHER_SERIES_ID);
        $voucher->save();
    }

    private function createSecondVoucher(): void
    {
        $voucher = oxNew(Voucher::class);
        $voucher->setId(self::SECOND_VOUCHER_ID);
        $voucher->oxvouchers__oxvouchernr = new Field(self::SECOND_VOUCHER_ID);
        $voucher->oxvouchers__oxvoucherserieid = new Field(self::VOUCHER_SERIES_ID);
        $voucher->save();
    }

    /**
     * Insert test user, set to session
     *
     * @return User
     */
    private function createUser(): User
    {
        $sTestUserId = substr_replace(Registry::getUtilsObject()->generateUId(), '_', 0, 1);

        $user = oxNew(User::class);
        $user->setId($sTestUserId);

        $user->oxuser__oxactive = new Field('1');
        $user->oxuser__oxrights = new Field('user');
        $user->oxuser__oxshopid = new Field(ShopIdCalculator::BASE_SHOP_ID);
        $user->oxuser__oxusername = new Field('testuser@oxideshop.dev');
        $user->oxuser__oxpassword = new Field(
            'c630e7f6dd47f9ad60ece4492468149bfed3da3429940181464baae99941d0ffa5562' .
            'aaecd01eab71c4d886e5467c5fc4dd24a45819e125501f030f61b624d7d'
        ); //password is asdfasdf
        $user->oxuser__oxpasssalt = new Field('3ddda7c412dbd57325210968cd31ba86');
        $user->oxuser__oxcustnr = new Field('667');
        $user->oxuser__oxfname = new Field('Erna');
        $user->oxuser__oxlname = new Field('Helvetia');
        $user->oxuser__oxstreet = new Field('Dorfstrasse');
        $user->oxuser__oxstreetnr = new Field('117');
        $user->oxuser__oxcity = new Field('Oberbuchsiten');
        $user->oxuser__oxcountryid = new Field('a7c40f631fc920687.20179984');
        $user->oxuser__oxzip = new Field('4625');
        $user->oxuser__oxsal = new Field('MRS');
        $user->oxuser__oxactive = new Field('1');
        $user->oxuser__oxboni = new Field('1000');
        $user->oxuser__oxcreate = new Field('2015-05-20 22:10:51');
        $user->oxuser__oxregister = new Field('2015-05-20 22:10:51');
        $user->oxuser__oxboni = new Field('1000');

        $user->save();

        $group = oxNew(Object2Group::class);
        $group->oxobject2group__oxobjectid = new Field($user->getId());
        $group->oxobject2group__oxgroupsid = new Field(self::GROUP_ID);
        $group->save();

        return $user;
    }

    private function addVoucherSeriesToUserGroup(): void
    {
        $group = oxNew(Object2Group::class);
        $group->oxobject2group__oxid = substr_replace(Registry::getUtilsObject()->generateUId(), '_', 0, 1);
        $group->oxobject2group__oxshopid = 1;
        $group->oxobject2group__oxobjectid = new Field(self::VOUCHER_SERIES_ID);
        $group->oxobject2group__oxgroupsid = new Field(self::GROUP_ID);
        $group->save();
    }

    /**
     * @param string $voucherId
     * @param string $articleId
     *
     */
    private function addVoucherToSpecificProduct(string $voucherId, string $articleId): void
    {
        $object2Discount = oxNew(BaseModel::class);
        $object2Discount->init('oxobject2discount');
        $object2Discount->oxobject2discount__oxdiscountid = new Field($voucherId);
        $object2Discount->oxobject2discount__oxobjectid = new Field($articleId);
        $object2Discount->oxobject2discount__oxtype = new Field(Article::class);

        $object2Discount->save();
    }

    /**
     *
     * @return string
     */
    private function loginUser(): string
    {
        $this->setRequestParameter('lgn_usr', 'testuser@oxideshop.dev');
        $this->setRequestParameter('lgn_pwd', 'asdfasdf');
        $oCmpUser = oxNew('oxcmp_user');
        return $oCmpUser->login();
    }

    private function checkVoucherWithoutLogin(): void
    {
        $basket = oxNew(Basket::class);
        $basket->addToBasket(self::FIRST_ARTICLE, 1);

        $basket->calculateBasket(true);
        $this->assertSame(11.76, $basket->getNettoSum());

        // test if voucher works without user login

        $basket->addVoucher(self::FIRST_VOUCHER_ID);
        $basket->calculateBasket(true);
        // it is not working because voucher bind to specific user
        $this->assertSame(11.76, $basket->getNettoSum());
    }

    private function checkVoucherWithUserLogin(): Basket
    {
        $this->loginUser();

        $basket = oxNew(Basket::class);
        $basket->addToBasket(self::FIRST_ARTICLE, 1);
        $basket->addVoucher(self::FIRST_VOUCHER_ID);

        // test if voucher works after login because it is belong to this user group
        $basket->calculateBasket(true);
        $this->assertSame(7.56, $basket->getNettoSum());

        return $basket;
    }

    private function checkIfVoucherCalculateOnce(Basket $basket): Basket
    {
        // Add voucher one more time
        $basket->addVoucher(self::FIRST_VOUCHER_ID);

        $basket->calculateBasket(true);
        $this->assertSame(7.56, $basket->getNettoSum());

        return $basket;
    }

    private function checkMultipleVouchersInSameUserGroup(Basket $basket): Basket
    {
        $basket->addToBasket(self::SECOND_ARTICLE,1);

        $basket->calculateBasket(true);
        $this->assertSame(12.61, $basket->getNettoSum());

        // Add second product
        $basket->addToBasket(self::SECOND_ARTICLE, 1);
        $basket->calculateBasket(true);
        $this->assertSame(17.65, $basket->getNettoSum());

        // Add second voucher
        $basket->addVoucher(self::SECOND_VOUCHER_ID);

        $basket->calculateBasket(true);
        $this->assertSame(13.45, $basket->getNettoSum());

        return $basket;
    }

}
