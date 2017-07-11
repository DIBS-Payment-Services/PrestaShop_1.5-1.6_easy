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

namespace Invertus\Dibs\Action;

use Cart;
use Currency;
use Dibs;
use DibsOrderPayment;
use Invertus\Dibs\Adapter\LinkAdapter;
use Invertus\Dibs\Payment\PaymentCreateRequest;
use Invertus\Dibs\Service\PaymentService;

/**
 * Class PaymentCreateAction
 *
 * @package Invertus\Dibs\Action
 */
class PaymentCreateAction extends AbstractAction
{
    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @var LinkAdapter
     */
    private $linkAdapter;

    /**
     * @var Dibs
     */
    private $module;

    /**
     * @var array
     */
    private $supportedCountries;

    /**
     * PaymentCreateAction constructor.
     *
     * @param PaymentService $paymentService
     * @param LinkAdapter $linkAdapter
     * @param Dibs $module
     * @param array $supportedCountries
     */
    public function __construct(
        PaymentService $paymentService,
        LinkAdapter $linkAdapter,
        Dibs $module,
        array $supportedCountries
    ) {
        $this->paymentService = $paymentService;
        $this->linkAdapter = $linkAdapter;
        $this->module = $module;
        $this->supportedCountries = $supportedCountries;
    }

    /**
     * Create payment for given order
     *
     * @param Cart $cart
     *
     * @return DibsOrderPayment|false
     */
    public function createPayment(Cart $cart)
    {
        $currency = new Currency($cart->id_currency);

        $createRequest = new PaymentCreateRequest();
        $createRequest->setAmount($cart->getOrderTotal());
        $createRequest->setCurrency($currency->iso_code);
        $createRequest->setReference($cart->id);
        $createRequest->setUrl($this->linkAdapter->getModuleLink('dibs', 'checkout'));
        $createRequest->setShippingCountries($this->supportedCountries);

        $items = $this->getCartProductItems($cart);
        $createRequest->setItems($items);

        $additionalItems = $this->getCartAdditionalItems($cart);
        foreach ($additionalItems as $item) {
            $createRequest->addItem($item);
        }

        $paymentId = $this->paymentService->createPayment($createRequest);
        if (!$paymentId) {
            return false;
        }

        $orderPayment = new DibsOrderPayment();
        $orderPayment->id_payment = $paymentId;
        $orderPayment->id_cart = $cart->id;
        $orderPayment->save();

        return $orderPayment;
    }

    /**
     * Module instance used for translations
     *
     * @return \Dibs
     */
    protected function getModule()
    {
        return $this->module;
    }
}
