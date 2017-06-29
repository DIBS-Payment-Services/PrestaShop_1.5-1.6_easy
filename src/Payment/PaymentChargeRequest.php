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

namespace Invertus\Dibs\Payment;

/**
 * Class PaymentChargeRequest
 *
 * @package Invertus\Dibs\Payment
 */
class PaymentChargeRequest
{
    /**
     * @var int Total order amount with TAX in cents
     */
    private $amount;

    /**
     * @var PaymentItem[]|array
     */
    private $items = array();

    /**
     * @var string Payment ID to charge
     */
    private $paymentId;

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
     * @param PaymentItem $items
     */
    public function addItem(PaymentItem $items)
    {
        $this->items[] = $items;
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
            }, $this->getItems())
        );
    }

    /**
     * @return string
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @param string $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * @param PaymentItem[]|array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }
}
