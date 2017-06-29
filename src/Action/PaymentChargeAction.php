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
use Dibs;
use Invertus\Dibs\Adapter\ConfigurationAdapter;
use Invertus\Dibs\Payment\PaymentChargeRequest;
use Invertus\Dibs\Repository\OrderPaymentRepository;
use Invertus\Dibs\Service\PaymentService;
use Order;

/**
 * Class PaymentChargeAction
 *
 * @package Invertus\Dibs\Action
 */
class PaymentChargeAction extends AbstractAction
{
    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @var OrderPaymentRepository
     */
    private $orderPaymentRepository;

    /**
     * @var ConfigurationAdapter
     */
    private $configurationAdapter;

    /**
     * @var Dibs
     */
    private $module;

    /**
     * PaymentCancelAction constructor.
     *
     * @param PaymentService $paymentService
     * @param OrderPaymentRepository $orderPaymentRepository
     * @param ConfigurationAdapter $configurationAdapter
     * @param Dibs $module
     */
    public function __construct(
        PaymentService $paymentService,
        OrderPaymentRepository $orderPaymentRepository,
        ConfigurationAdapter $configurationAdapter,
        Dibs $module
    ) {
        $this->paymentService = $paymentService;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->configurationAdapter = $configurationAdapter;
        $this->module = $module;
    }

    /**
     * Charge payment
     *
     * @param Order $order
     * @return bool
     */
    public function chargePayment(Order $order)
    {
        if ('dibs' != $order->module) {
            return false;
        }

        $orderPayment = $this->orderPaymentRepository->findOrderPaymentByOrderId($order->id);
        if (!$orderPayment || !$orderPayment->canBeCharged()) {
            return false;
        }

        $chargeRequest = new PaymentChargeRequest();
        $chargeRequest->setAmount($order->total_paid_tax_incl);
        $chargeRequest->setPaymentId($orderPayment->id_payment);

        $items = $this->getOrderProductItems($order);
        $chargeRequest->setItems($items);

        $additionalItems = $this->getOrderAdditionalItems($order);
        foreach ($additionalItems as $item) {
            $chargeRequest->addItem($item);
        }

        $chargeId = $this->paymentService->chargePayment($chargeRequest);
        if (!$chargeId) {
            return false;
        }

        $orderPayment->id_charge = $chargeId;
        $orderPayment->is_charged = 1;
        $orderPayment->save();

        $idOrderState = $this->configurationAdapter->getCompletedOrderStateId();
        $order->setCurrentState($idOrderState);

        return true;
    }

    /**
     * Charge multiple payments
     *
     * @param array|int[] $idOrders
     *
     * @return array|bool
     */
    public function chargePayments(array $idOrders)
    {
        $collection = new \Collection('Order');
        $collection->where('id_order', 'in', $idOrders);
        $orders = $collection->getResults();

        $result = array();
        $success = false;

        /** @var Order $order */
        foreach ($orders as $order) {
            if (!$this->chargePayment($order)) {
                $result[] = $order->id;
                continue;
            }

            $success = true;
        }

        if (!empty($result)) {
            return $success ? $result : false;
        }

        return true;
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
