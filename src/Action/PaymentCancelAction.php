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
use Invertus\Dibs\Payment\PaymentCancelRequest;
use Invertus\Dibs\Repository\OrderPaymentRepository;
use Invertus\Dibs\Service\PaymentService;
use Order;

/**
 * Class PaymentCancelAction
 *
 * @package Invertus\Dibs\Action
 */
class PaymentCancelAction extends AbstractAction
{
    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @var ConfigurationAdapter
     */
    private $configurationAdapter;

    /**
     * @var OrderPaymentRepository
     */
    private $orderPaymentRepository;

    /**
     * @var Dibs
     */
    private $module;

    /**
     * PaymentCancelAction constructor.
     *
     * @param PaymentService $paymentService
     * @param ConfigurationAdapter $configurationAdapter
     * @param OrderPaymentRepository $orderPaymentRepository
     * @param Dibs $module
     */
    public function __construct(
        PaymentService $paymentService,
        ConfigurationAdapter $configurationAdapter,
        OrderPaymentRepository $orderPaymentRepository,
        Dibs $module
    ) {
        $this->paymentService = $paymentService;
        $this->configurationAdapter = $configurationAdapter;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->module = $module;
    }

    /**
     * Cancel cart payment in DIBS
     *
     * @param Cart $cart
     *
     * @return bool
     */
    public function cancelCartPayment(Cart $cart)
    {
        $orderPayment = $this->orderPaymentRepository->findOrderPaymentByCartId($cart->id);
        if (!$orderPayment || !$orderPayment->canBeCanceled()) {
            return false;
        }

        $cancelRequest = new PaymentCancelRequest();
        $cancelRequest->setAmount($cart->getOrderTotal());
        $cancelRequest->setPaymentId($orderPayment->id_payment);

        $items = $this->getCartProductItems($cart);
        $cancelRequest->setItems($items);

        $additionalItems = $this->getCartAdditionalItems($cart);
        foreach ($additionalItems as $item) {
            $cancelRequest->addItem($item);
        }

        $success = $this->paymentService->cancelPayment($cancelRequest);
        if (!$success) {
            return false;
        }

        if ($orderPayment->id_order) {
            $cancelOrderStateId = $this->configurationAdapter->getCanceledOrderStateId();

            $order = new Order($orderPayment->id_order);
            $order->setCurrentState($cancelOrderStateId);
        }

        $orderPayment->is_canceled = 1;
        $orderPayment->save();

        return true;
    }

    /**
     * Cancel order payment
     *
     * @param Order $order
     *
     * @return bool
     */
    public function cancelOrderPayment(Order $order)
    {
        $orderPayment = $this->orderPaymentRepository->findOrderPaymentByOrderId($order->id);
        if (!$orderPayment || !$orderPayment->canBeCanceled()) {
            return false;
        }

        $cancelRequest = new PaymentCancelRequest();
        $cancelRequest->setAmount($order->total_paid_tax_incl);
        $cancelRequest->setPaymentId($orderPayment->id_payment);

        $items = $this->getOrderProductItems($order);
        $cancelRequest->setItems($items);

        $additionalItems = $this->getOrderAdditionalItems($order);
        foreach ($additionalItems as $item) {
            $cancelRequest->addItem($item);
        }

        $success = $this->paymentService->cancelPayment($cancelRequest);
        if (!$success) {
            return false;
        }

        if ($orderPayment->id_order) {
            $cancelOrderStateId = $this->configurationAdapter->getCanceledOrderStateId();

            $order = new Order($orderPayment->id_order);
            $order->setCurrentState($cancelOrderStateId);
        }

        $orderPayment->is_canceled = 1;
        $orderPayment->save();

        return true;
    }

    /**
     * Cancel multiple order payments
     *
     * @param array $orderIds
     *
     * @return bool|array If all payments canceled ok, then TRUE, if all failed then FALSE, if some failed, then array
     */
    public function cancelOrderPayments(array $orderIds)
    {
        $collection = new \Collection('Order');
        $collection->where('id_order', 'in', $orderIds);
        $orders = $collection->getResults();

        $result = array();
        $success = false;

        /** @var Order $order */
        foreach ($orders as $order) {
            if (!$this->cancelOrderPayment($order)) {
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
     * @return Dibs
     */
    protected function getModule()
    {
        return $this->module;
    }
}
