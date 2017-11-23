<?php
/**
 * 2016 - 2017 Invertus, UAB
 *
 * NOTICE OF LICENSE
 *
 * This file is proprietary and can not be copied and/or distributed
 * without the express permission of INVERTUS, UAB
 *
 * @author    INVERTUS, UAB www.invertus.eu <support@invertus.eu>
 * @copyright Copyright (c) permanent, INVERTUS, UAB
 * @license   Addons PrestaShop license limitation
 *
 * International Registered Trademark & Property of INVERTUS, UAB
 */

namespace Invertus\DibsEasy\Payment;

/**
 * Class PaymentItem
 *
 * @package Invertus\DibsEasy\Payment
 */
class PaymentItem
{
    /**
     * @var string Product reference
     */
    private $reference;

    /**
     * @var string Product name
     */
    private $name;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var string
     */
    private $unit = 'pcs';

    /**
     * @var int Unit price tax excluded in cents
     */
    private $unitPrice;

    /**
     * @var int If 25%, then pass 2500
     */
    private $taxRate;

    /**
     * @var int Total TAX amount of order in cents
     */
    private $taxAmount;

    /**
     * @var int Total amount including tax in cents
     */
    private $grossTotalAmount;

    /**
     * @var int Total amount exluding tax in cents
     */
    private $netTotalAmount;

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return int
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param int $unitPrice
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = (int) (string) ((float) $unitPrice * 100);
    }

    /**
     * @return int
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @param int $taxRate
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = (int) ($taxRate * 100);
    }

    /**
     * @return int
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * @param int $taxAmount
     */
    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = (int) (string) ($taxAmount * 100);
    }

    /**
     * @return int
     */
    public function getGrossTotalAmount()
    {
        return $this->grossTotalAmount;
    }

    /**
     * @param int $grossTotalAmount
     */
    public function setGrossTotalAmount($grossTotalAmount)
    {
        $this->grossTotalAmount = (int) (string) ($grossTotalAmount * 100);
    }

    /**
     * @return int
     */
    public function getNetTotalAmount()
    {
        return $this->netTotalAmount;
    }

    /**
     * @param int $netTotalAmount
     */
    public function setNetTotalAmount($netTotalAmount)
    {
        $this->netTotalAmount = (int) (string) ($netTotalAmount * 100);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $itemArray = array(
            'reference' => $this->getReference(),
            'name' => $this->getName(),
            'quantity' => $this->getQuantity(),
            'unit' => $this->getUnit(),
            'unitPrice' => $this->getUnitPrice(),
            'taxRate' => $this->getTaxRate(),
            'taxAmount' => $this->getTaxAmount(),
            'grossTotalAmount' => $this->getGrossTotalAmount(),
            'netTotalAmount' => $this->getNetTotalAmount(),
        );

        return $itemArray;
    }
}
