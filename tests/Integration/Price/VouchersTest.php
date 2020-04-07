<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Integration\Price;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Voucher;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Application\Model\VoucherSerie;
use OxidTestCase;

class VouchersTest extends OxidTestCase
{
    private const VOUCHER_NUMBER = 101;
    private const ARTICLE_WITH_VOUCHER = '1951';
    private const ARTICLE_WITHOUT_VOUCHER = '1952';

    public function setUp()
    {
        $voucher = $this->createVoucher();
        $this->addVoucherToDiscount($voucher->getId(), self::ARTICLE_WITH_VOUCHER);
    }

    public function testVoucherWithSpecificProductInBasket(): void
    {
        $basket = oxNew(Basket::class);
        $basket->addToBasket(self::ARTICLE_WITH_VOUCHER, 1);

        $basket->calculateBasket(true);
        $this->assertSame(11.76, $basket->getNettoSum());
        $this->assertSame(14.0, $basket->getBruttoSum());

        $basket->addVoucher(self::VOUCHER_NUMBER);

        // applying voucher will affect the basket calculation
        $basket->calculateBasket(true);
        $this->assertSame(10.59, $basket->getNettoSum());
        $this->assertSame(14.0, $basket->getBruttoSum());

        // test voucher with a not bind product
        $basket = oxNew(Basket::class);
        $basket->addToBasket(self::ARTICLE_WITHOUT_VOUCHER,1);

        $basket->calculateBasket(true);
        $this->assertSame(5.04, $basket->getNettoSum());
        $this->assertSame(6.0, $basket->getBruttoSum());

        $basket->addVoucher(self::VOUCHER_NUMBER);

        // applying voucher does not affect basket
        $basket->calculateBasket(true);
        $this->assertSame(5.04, $basket->getNettoSum());
        $this->assertSame(6.0, $basket->getBruttoSum());
    }

    public function testVouchersInBasketNotBindToSpecificProduct(): void
    {
        $basket = oxNew(Basket::class);
        $basket->addToBasket(self::ARTICLE_WITH_VOUCHER, 1);

        $basket->calculateBasket(true);
        $this->assertSame(11.76, $basket->getNettoSum());
        $this->assertSame(14.0, $basket->getBruttoSum());

        $basket->addVoucher(self::VOUCHER_NUMBER);
        $basket->calculateBasket(true);

        $this->assertSame(10.59, $basket->getNettoSum());
        $this->assertSame(14.0, $basket->getBruttoSum());
    }


    private function createVoucher(): Voucher
    {
        $startDate = date('Y-m-d 00:00:00', time() - 86400);
        $endDate = date('Y-m-d 00:00:00', time() + 86400);

        $voucherSeries = oxNew(VoucherSerie::class);
        $voucherSeries->setId('testId');
        $voucherSeries->oxvoucherseries__oxshopid = new Field('1');
        $voucherSeries->oxvoucherseries__oxserienr = new Field('voucher_series_relative');
        $voucherSeries->oxvoucherseries__oxseriedescription = new Field('20 percent');
        $voucherSeries->oxvoucherseries__oxdiscount = new Field(10);
        $voucherSeries->oxvoucherseries__oxdiscounttype = new Field('relative');
        $voucherSeries->oxvoucherseries__oxbegindate = new Field($startDate);
        $voucherSeries->oxvoucherseries__oxenddate = new Field($endDate);
        $voucherSeries->oxvoucherseries__oxallowsameseries = new Field('1');
        $voucherSeries->oxvoucherseries__oxalowotherseries = new Field('0');
        $voucherSeries->oxvoucherseries__oxallowuseanother = new Field('1');
        $voucherSeries->oxvoucherseries__oxminimumvalue = new Field('0.00');
        $voucherSeries->oxvoucherseries__oxcalculateonce = new Field('1');
        $voucherSeries->save();

        $voucher = oxNew(Voucher::class);
        $voucher->setId('testId');
        $voucher->oxvouchers__oxvouchernr = new Field(self::VOUCHER_NUMBER);
        $voucher->oxvouchers__oxvoucherserieid = new Field('testId');
        $voucher->save();

        return $voucher;
    }


    /**
     * @param string $voucherId
     * @param string $articleId
     *
     * @throws \Exception
     */
    private function addVoucherToDiscount(string $voucherId, string $articleId): void
    {
        $object2Discount = oxNew(BaseModel::class);
        $object2Discount->init('oxobject2discount');
        $object2Discount->oxobject2discount__oxdiscountid = new Field($voucherId);
        $object2Discount->oxobject2discount__oxobjectid = new Field($articleId);
        $object2Discount->oxobject2discount__oxtype = new Field(Article::class);

        $object2Discount->save();
    }

}
