<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Price;

use DateTime;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\Object2Group;
use OxidEsales\Eshop\Application\Model\SelectList;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\Voucher;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Application\Model\VoucherSerie;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\ShopIdCalculator;
use OxidEsales\TestingLibrary\UnitTestCase;

final class VouchersUserAndProductBindingTest extends UnitTestCase
{
    private const FIRST_VOUCHER_ID = 'testVoucherId1';
    private const SECOND_VOUCHER_ID = 'testVoucherId2';
    private const THIRD_VOUCHER_ID = 'testVoucherId3';
    private const FIRST_VOUCHER_SERIES_ID = 'testVoucherSeries1';
    private const SECOND_VOUCHER_SERIES_ID = 'testVoucherSeries2';
    private const FIRST_ARTICLE_ID = '101';
    private const SECOND_ARTICLE_ID = '102';
    private const THIRD_ARTICLE_ID = '103';
    private const GROUP_ID = 'oxidnewcustomer';
    private const FIRST_TEST_CATEGORY_ID = 'testCategory1';
    private const SECOND_TEST_CATEGORY_ID = 'testCategory2';
    private const FIRST_VOUCHER_NUMBER = '1111';
    private const SECOND_VOUCHER_NUMBER = '2222';

    protected function setUp(): void
    {
        $this->createArticle(self::FIRST_ARTICLE_ID, 14);
        $this->createArticle(self::SECOND_ARTICLE_ID, 6);
        $this->createArticle(self::THIRD_ARTICLE_ID, 10);

        $selectionList = oxNew(SelectList::class);
        $selectionList->oxselectlist__oxshopid = new Field($this->getConfig()->getShopId());
        $selectionList->oxselectlist__oxtitle = new Field('testsellist');
        $selectionList->oxselectlist__oxident = new Field('testsellist');
        $selectionList->oxselectlist__oxvaldesc = new Field('testsellist');
        $selectionList->save();

        $oNewGroup = oxNew(BaseModel::class);
        $oNewGroup->init("oxobject2selectlist");
        $oNewGroup->oxobject2selectlist__oxobjectid = new Field(self::FIRST_ARTICLE_ID);
        $oNewGroup->oxobject2selectlist__oxselnid = new Field('testsellist');
        $oNewGroup->oxobject2selectlist__oxsort = new Field(0);
        $oNewGroup->save();

        $this->createVoucherSeries(self::FIRST_VOUCHER_SERIES_ID, 5);
        $this->createVoucherSeries(self::SECOND_VOUCHER_SERIES_ID, 10);
        
        $this->addVoucherToSeries(
            self::FIRST_VOUCHER_ID,
            self::FIRST_VOUCHER_SERIES_ID,
            self::FIRST_VOUCHER_NUMBER
        );
        $this->addVoucherToSeries(
            self::SECOND_VOUCHER_ID,
            self::FIRST_VOUCHER_SERIES_ID,
            self::FIRST_VOUCHER_NUMBER
        );
        $this->addVoucherToSeries(
            self::THIRD_VOUCHER_ID,
            self::SECOND_VOUCHER_SERIES_ID,
            self::SECOND_VOUCHER_NUMBER
        );

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testVoucherForSpecificCategory(): void
    {
        $this->createUser();
        $this->loginUser();

        $this->createCategory(self::FIRST_TEST_CATEGORY_ID, 'Test Title 1');
        $this->createCategory(self::SECOND_TEST_CATEGORY_ID, 'Test Title 2');
        $this->addArticleToCategory(self::FIRST_ARTICLE_ID, self::FIRST_TEST_CATEGORY_ID);
        $this->addArticleToCategory(self::SECOND_ARTICLE_ID, self::FIRST_TEST_CATEGORY_ID);
        $this->addArticleToCategory(self::THIRD_ARTICLE_ID, self::SECOND_TEST_CATEGORY_ID);
        $this->assignVoucherToCategory(self::FIRST_VOUCHER_SERIES_ID, self::FIRST_TEST_CATEGORY_ID);
        $this->assignVoucherToCategory(self::SECOND_VOUCHER_SERIES_ID, self::SECOND_TEST_CATEGORY_ID);

        // Voucher and product in basket Are not in same category so voucher does not work
        $basket = oxNew(Basket::class);
        $basket->addToBasket(self::FIRST_ARTICLE_ID, 1, array(0));
        $basket->addToBasket(self::SECOND_ARTICLE_ID, 1, array(0));

        $basket->calculateBasket(true);
        $this->assertSame(16.81, $basket->getNettoSum());

        $basket->addVoucher(self::SECOND_VOUCHER_NUMBER);

        $basket->calculateBasket(true);
        $this->assertSame(16.81, $basket->getNettoSum());

        // Apply a voucher that in same category

        $basket->addVoucher(self::FIRST_VOUCHER_NUMBER);
        $basket->calculateBasket(true);
        $this->assertSame(12.61, $basket->getNettoSum());
    }
    public function testVoucherForSpecificProduct(): void
    {
        $this->assignVoucherSeriesToArticle(self::FIRST_VOUCHER_SERIES_ID, self::FIRST_ARTICLE_ID);
        $this->assignVoucherSeriesToArticle(self::FIRST_VOUCHER_SERIES_ID, self::SECOND_ARTICLE_ID);
        $this->assignVoucherSeriesToArticle(self::SECOND_VOUCHER_SERIES_ID, self::THIRD_ARTICLE_ID);

        // Voucher and product in basket Are not in same category so voucher does not work
        $basket = oxNew(Basket::class);
        $basket->addToBasket(self::FIRST_ARTICLE_ID, 1);
        $basket->addToBasket(self::SECOND_ARTICLE_ID, 1);

        $basket->calculateBasket(true);
        $this->assertSame(16.81, $basket->getNettoSum());

        $basket->addVoucher(self::SECOND_VOUCHER_NUMBER);

        $basket->calculateBasket(true);
        $this->assertSame(16.81, $basket->getNettoSum());

        // Apply a voucher that in same category as products

        $basket->addVoucher(self::FIRST_VOUCHER_NUMBER);
        $basket->calculateBasket(true);
        $this->assertSame(12.61, $basket->getNettoSum());
    }

    public function testVoucherIfAssignedToSpecificUserGroup(): void
    {
        $this->createUser();
        $this->assignVoucherSeriesToUserGroup(self::FIRST_VOUCHER_SERIES_ID);

        // If user not login yet voucher should not work
        $basket = oxNew(Basket::class);
        $basket->addToBasket(self::FIRST_ARTICLE_ID, 1);

        $basket->calculateBasket(true);
        $this->assertSame(11.76, $basket->getNettoSum());

        $basket->addVoucher(self::FIRST_VOUCHER_NUMBER);
        $basket->calculateBasket(true);
        $this->assertSame(11.76, $basket->getNettoSum());


        $this->loginUser();

        // After login voucher should work and reduce the basket price

        $basket = oxNew(Basket::class);
        $basket->addToBasket(self::FIRST_ARTICLE_ID, 1);
        $basket->addVoucher(self::FIRST_VOUCHER_NUMBER);
        // test if voucher works after login because it is belong to this user group
        $basket->calculateBasket(true);
        $this->assertSame(7.56, $basket->getNettoSum());
    }

    public function testIfVoucherApplyOnlyForOneProductInTheBasket(): void
    {
        $basket = oxNew(Basket::class);
        $basket->addToBasket(self::FIRST_ARTICLE_ID, 1);

        $basket->addVoucher(self::FIRST_VOUCHER_NUMBER);

        $basket->calculateBasket(true);
        $this->assertSame(7.56, $basket->getNettoSum());

        $basket->addToBasket(self::SECOND_ARTICLE_ID, 1);
        $basket->calculateBasket(true);
        $this->assertSame(12.61, $basket->getNettoSum());
    }

    private function createVoucherSeries(string $seriesId, int $discount): void
    {
        $startDate = (new DateTime())->modify('-1 day')->format('Y-m-d 00:00:00');
        $endDate = (new DateTime())->modify('+1 day')->format('Y-m-d 00:00:00');

        $voucherSeries = oxNew(VoucherSerie::class);
        $voucherSeries->setId($seriesId);
        $voucherSeries->oxvoucherseries__oxshopid = new Field('1');
        $voucherSeries->oxvoucherseries__oxdiscount = new Field($discount);
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
    private function addVoucherToSeries(string $voucherId, string $seriesId, string $voucherNumber): void
    {
        $voucher = oxNew(Voucher::class);
        $voucher->setId($voucherId);
        $voucher->oxvouchers__oxvouchernr = new Field($voucherNumber);
        $voucher->oxvouchers__oxvoucherserieid = new Field($seriesId);
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

    private function assignVoucherSeriesToUserGroup(string $seriesId): void
    {
        $group = oxNew(Object2Group::class);
        $group->oxobject2group__oxid = substr_replace(Registry::getUtilsObject()->generateUId(), '_', 0, 1);
        $group->oxobject2group__oxshopid = 1;
        $group->oxobject2group__oxobjectid = new Field($seriesId);
        $group->oxobject2group__oxgroupsid = new Field(self::GROUP_ID);
        $group->save();
    }

    private function assignVoucherToCategory(string $voucherId, string $categoryId): void
    {
        $object2Discount = oxNew(BaseModel::class);
        $object2Discount->init('oxobject2discount');
        $object2Discount->oxobject2discount__oxdiscountid = new Field($voucherId);
        $object2Discount->oxobject2discount__oxobjectid = new Field($categoryId);
        $object2Discount->oxobject2discount__oxtype = new Field('oxcategories');

        $object2Discount->save();
    }

    private function loginUser(): void
    {
        $_POST['lgn_usr'] = 'testuser@oxideshop.dev';
        $_POST['lgn_pwd'] = 'asdfasdf';
        $oCmpUser = oxNew('oxcmp_user');
        $oCmpUser->login();
    }

    private function createArticle(string $articleId, int $price): void
    {
        $oArticle = oxNew(Article::class);
        $oArticle->setAdminMode(null);
        $oArticle->setId($articleId);
        $oArticle->oxarticles__oxprice = new Field($price);
        $oArticle->oxarticles__oxshopid = new Field(Registry::getConfig()->getBaseShopId());
        $oArticle->oxarticles__oxtitle = new Field('test_' . $articleId);
        $oArticle->oxarticles__oxstock = new Field(100);
        $oArticle->oxarticles__oxactive = new Field('1');
        $oArticle->save();
    }

    private function createCategory(string $categoryId, string $title): void
    {
        $category = oxNew(Category::class);
        $category->setId($categoryId);
        $category->oxcategories__oxparentid = new Field('oxrootid');
        $category->oxcategories__oxrootid = new Field($categoryId);
        $category->oxcategories__oxactive = new Field(1);
        $category->oxcategories__oxhidden = new Field(0);
        $category->oxcategories__oxleft = new Field('1');
        $category->oxcategories__oxright = new Field('2');
        $category->oxcategories__oxshopid = new Field(Registry::getConfig()->getBaseShopId());
        $category->oxcategories__oxtitle = new Field($title);
        $category->save();
    }

    private function addArticleToCategory(string $articleId, string $categoryId): void
    {
        $category = oxNew(BaseModel::class);
        $category->init('oxobject2category');
        $category->setId(substr_replace(Registry::getUtilsObject()->generateUId(), '_', 0, 1));
        $category->oxobject2category__oxobjectid = new Field($articleId);
        $category->oxobject2category__oxcatnid = new Field($categoryId);
        $category->oxobject2category__oxtime = new Field(time());

        $category->save();
    }

    private function assignVoucherSeriesToArticle(string $seriesId, string $articleId): void
    {
        $object2Discount = oxNew(BaseModel::class);
        $object2Discount->init('oxobject2discount');
        $object2Discount->oxobject2discount__oxdiscountid = new Field($seriesId);
        $object2Discount->oxobject2discount__oxobjectid = new Field($articleId);
        $object2Discount->oxobject2discount__oxtype = new Field('oxarticles');

        $object2Discount->save();
    }
}
