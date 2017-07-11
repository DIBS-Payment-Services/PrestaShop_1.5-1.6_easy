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
 * Class PaymentOrder
 *
 * @package Invertus\Dibs\Payment
 */
class PaymentCreateRequest
{
    /**
     * @var PaymentItem[]
     */
    private $items = array();

    /**
     * @var int Total amount of order including TAX in cents
     */
    private $amount;

    /**
     * @var string Currency code of order in 3-letter (e.g. EUR)
     */
    private $currency;

    /**
     * @var int PrestaShop order ID
     */
    private $reference;

    /**
     * @var string URL of the checkout, needed when creating payment
     */
    private $url;

    /**
     * @var array
     */
    private $supportedCountries = array();

    /**
     * @param array $supportedCountries
     */
    public function setShippingCountries(array $supportedCountries)
    {
        $this->supportedCountries = $supportedCountries;
    }

    /**
     * @return PaymentItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param PaymentItem $paymentItem
     */
    public function addItem(PaymentItem $paymentItem)
    {
        $this->items[] = $paymentItem;
    }

    /**
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

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
        // For insane casting see http://php.net/language.types.float
        $this->amount = (int) (string) ((float) $amount * 100);
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param int $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $orderArray = array(
            'order' => array(
                'amount' => $this->getAmount(),
                'currency' => $this->getCurrency(),
                'reference' => $this->getReference(),
                'items' => array_map(function (PaymentItem $item) {
                    return $item->toArray();
                }, $this->getItems()),
            ),
            'checkout' => array(
                'url' => $this->getUrl(),
            ),
        );

        if (count($this->supportedCountries)) {
            $orderArray['checkout']['ShippingCountries'] = array_map(function($countryCode) {
                return ['countryCode' => $countryCode];
           }, $this->supportedCountries);
        }

        return $orderArray;
    }
}
