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
 * Class PaymentRefundRequest
 *
 * @package Invertus\DibsEasy\Payment
 */
class PaymentRefundRequest
{
    /**
     * @var int Total order amount with TAXES in cents
     */
    private $amount;

    /**
     * @var PaymentItem[]|array
     */
    private $items;

    /**
     * @var string Charge ID in DIBS
     */
    private $chargeId;

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = (int) (string) ($amount * 100);
    }

    /**
     * @return array|PaymentItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param PaymentItem $item
     */
    public function addItem(PaymentItem $item)
    {
        $this->items[] = $item;
    }

    /**
     * @param array|PaymentItem[] $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return string
     */
    public function getChargeId()
    {
        return $this->chargeId;
    }

    /**
     * @param string $chargeId
     */
    public function setChargeId($chargeId)
    {
        $this->chargeId = $chargeId;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'amount' => $this->getAmount(),
            'orderItems' => array_map(function (PaymentItem $item) {
                return $item->toArray();
            }, $this->getItems()),
        );
    }
}
