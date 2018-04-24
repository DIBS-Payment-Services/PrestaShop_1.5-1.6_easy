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

namespace Invertus\DibsEasy\Action;

use Cart;
use Currency;
use DibsOrderPayment;
use Invertus\DibsEasy\Adapter\ConfigurationAdapter;
use Invertus\DibsEasy\Adapter\LinkAdapter;
use Invertus\DibsEasy\Payment\PaymentCreateRequest;
use Invertus\DibsEasy\Service\PaymentService;
use Invertus\DibsEasy\ValueObject\Consumer;
use Module;

/**
 * Class PaymentCreateAction
 *
 * @package Invertus\DibsEasy\Action
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
     * @var Module
     */
    private $module;

    /**
     * @var array
     */
    private $supportedCountries;

    /**
     * @var ConfigurationAdapter
     */
    private $configuration;

    /**
     * PaymentCreateAction constructor.
     *
     * @param PaymentService $paymentService
     * @param LinkAdapter $linkAdapter
     * @param Module $module
     * @param ConfigurationAdapter $configuration
     * @param array $supportedCountries
     */
    public function __construct(
        PaymentService $paymentService,
        LinkAdapter $linkAdapter,
        Module $module,
        ConfigurationAdapter $configuration,
        array $supportedCountries
    ) {
        $this->paymentService = $paymentService;
        $this->linkAdapter = $linkAdapter;
        $this->module = $module;
        $this->supportedCountries = $supportedCountries;
        $this->configuration = $configuration;
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
        $createRequest->setUrl($this->linkAdapter->getModuleLink('dibseasy', 'checkout'));
        $createRequest->setTermsUrl($this->configuration->get('DIBS_TAC_URL'));

        $this->addConsumerData($createRequest);

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
     * Adds consumer data to request
     *
     * @param PaymentCreateRequest $request
     */
    protected function addConsumerData(PaymentCreateRequest $request)
    {
        $consumerType = $this->configuration->get('DIBS_CONSUMER_TYPE');

        switch ($consumerType) {
            case Consumer::b2bAndB2cWithDefaultB2bType():
                $supportedType = [Consumer::TYPE_B2B, Consumer::TYPE_B2C];
                $defaultType = Consumer::TYPE_B2B;
                break;
            case Consumer::b2cAndB2bWithDefaultB2cType():
                $supportedType = [Consumer::TYPE_B2C, Consumer::TYPE_B2B];
                $defaultType = Consumer::TYPE_B2C;
                break;
            case Consumer::TYPE_B2B:
                $supportedType = [Consumer::TYPE_B2B];
                $defaultType = Consumer::TYPE_B2B;
                break;
            default:
            case Consumer::TYPE_B2C:
                $supportedType = [Consumer::TYPE_B2C];
                $defaultType = Consumer::TYPE_B2C;
                break;
        }

        $request->setSupportedConsumerTypes($supportedType);
        $request->setDefaultConsumerType($defaultType);
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
