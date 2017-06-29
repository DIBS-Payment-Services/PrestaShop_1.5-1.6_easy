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
use Invertus\Dibs\Payment\PaymentItem;
use Invertus\Dibs\Payment\PaymentRefundRequest;
use Invertus\Dibs\Repository\OrderPaymentRepository;
use Invertus\Dibs\Service\PaymentService;
use Order;

/**
 * Class PaymentRefundAction
 *
 * @package Invertus\Dibs\Action
 */
class PaymentRefundAction extends AbstractAction
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
     * Refund order payment
     *
     * @param Order $order
     *
     * @return bool
     */
    public function refundPayment(Order $order)
    {
        if ('dibs' != $order->module) {
            return false;
        }

        $orderPayment = $this->orderPaymentRepository->findOrderPaymentByOrderId($order->id);
        if (!$orderPayment || !$orderPayment->canBeRefunded()) {
            return false;
        }

        $refundRequest = new PaymentRefundRequest();
        $refundRequest->setAmount($order->total_paid_tax_incl);
        $refundRequest->setChargeId($orderPayment->id_charge);

        $items = $this->getOrderProductItems($order);
        $refundRequest->setItems($items);

        $additionalItems = $this->getOrderAdditionalItems($order);
        foreach ($additionalItems as $item) {
            $refundRequest->addItem($item);
        }

        $success = $this->paymentService->refundPayment($refundRequest);
        if (!$success) {
            return false;
        }

        $orderPayment->is_refunded = 1;
        $orderPayment->save();

        $idOrderState = $this->configurationAdapter->getRefundedOrderStateId();
        $order->setCurrentState($idOrderState);

        return true;
    }

    /**
     * Refund payment partially
     *
     * @param Order $order
     * @param array $refundDetails
     * @param float $shippingCostRefund
     *
     * @return bool
     */
    public function partialRefundPayment(Order $order, array $refundDetails, $shippingCostRefund)
    {
        if ('dibs' != $order->module) {
            return false;
        }

        $orderPayment = $this->orderPaymentRepository->findOrderPaymentByOrderId($order->id);
        if (!$orderPayment || !$orderPayment->canBePartiallyRefunded()) {
            return false;
        }

        $refundRequest = new PaymentRefundRequest();
        $refundRequest->setChargeId($orderPayment->id_charge);

        $idOrderDetails = array_keys($refundDetails);
        $collection = new \Collection('OrderDetail');
        $collection->where('id_order_detail', 'in', $idOrderDetails);
        $orderDetails = $collection->getResults();
        $totalAmount = 0;

        /** @var \OrderDetail $orderDetail */
        foreach ($orderDetails as $orderDetail) {
            $item = new PaymentItem();
            $item->setName($orderDetail->product_name);
            $item->setReference($orderDetail->product_reference);
            $item->setQuantity($refundDetails[$orderDetail->id]['quantity']);
            $item->setUnitPrice($refundDetails[$orderDetail->id]['unit_price']);
            $item->setTaxRate(0);
            $item->setTaxAmount(0);
            $item->setGrossTotalAmount($refundDetails[$orderDetail->id]['amount']);
            $item->setNetTotalAmount($refundDetails[$orderDetail->id]['amount']);

            $totalAmount += $item->getGrossTotalAmount();

            $refundRequest->addItem($item);
        }

        if ($shippingCostRefund) {
            $item = new PaymentItem();
            $item->setName($this->module->l('Shipping'));
            $item->setReference('shipping');
            $item->setQuantity(1);
            $item->setUnitPrice($shippingCostRefund);
            $item->setTaxRate(0);
            $item->setTaxAmount(0);
            $item->setGrossTotalAmount($shippingCostRefund);
            $item->setNetTotalAmount($shippingCostRefund);

            $totalAmount += $item->getGrossTotalAmount();

            $refundRequest->addItem($item);
        }

        $totalAmount = (float) (string) ($totalAmount / 100);
        $refundRequest->setAmount($totalAmount);

        $success = $this->paymentService->refundPayment($refundRequest);
        if (!$success) {
            return false;
        }

        if (!$orderPayment->is_partially_refunded) {
            $orderPayment->is_partially_refunded = 1;
            $orderPayment->save();
        }

        return true;
    }

    /**
     * Refund multiple orders
     *
     * @param array|int[] $idOrders
     *
     * @return array|bool
     */
    public function refundPayments(array $idOrders)
    {
        $collection = new \Collection('Order');
        $collection->where('id_order', 'in', $idOrders);
        $orders = $collection->getResults();

        $result = array();
        $success = false;

        /** @var Order $order */
        foreach ($orders as $order) {
            if (!$this->refundPayment($order)) {
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
