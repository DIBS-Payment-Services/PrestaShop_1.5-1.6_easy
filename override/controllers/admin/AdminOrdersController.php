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

class AdminOrdersController extends AdminOrdersControllerCore
{
    public function __construct()
    {
        parent::__construct();

        $this->bulk_actions['ChargePayments'] = array(
            'text' => $this->l('Charge Payments'),
        );

        $this->bulk_actions['CancelPayments'] = array(
            'text' => $this->l('Cancel Payments'),
        );

        $this->bulk_actions['RefundPayments'] = array(
            'text' => $this->l('Refund Payments'),
        );

        $this->addRowAction('chargePayment');
        $this->addRowAction('cancelPayment');
        $this->addRowAction('refundPayment');
    }

    public function initProcess()
    {
        parent::initProcess();

        if (empty($this->action)) {
            $this->action = Tools::getValue('action');
        }
    }

    public function processBulkChargePayments()
    {
        $idOrders = Tools::getValue('orderBox');

        /** @var Dibs $dibs */
        $dibs = Module::getInstanceByName('dibs');
        /** @var \Invertus\Dibs\Action\PaymentChargeAction $chargeAction */
        $chargeAction = $dibs->get('dibs.action.payment_charge');

        $result = $chargeAction->chargePayments($idOrders);
        if (is_array($result)) {
            $this->confirmations[] = $this->l('Payments successfully charged');
            $this->errors[] =
                sprintf($this->l('Failed to charge payment for orders with ID of %s'), implode(', ', $result));
            return;
        } elseif (false == $result) {
            $this->errors[] = $this->l('Failed to charge all payments');
            return;
        }

        $this->confirmations[] = $this->l('Payments successfully charged');
    }

    public function processBulkCancelPayments()
    {
        $idOrders = Tools::getValue('orderBox');

        /** @var Dibs $dibs */
        $dibs = Module::getInstanceByName('dibs');
        /** @var \Invertus\Dibs\Action\PaymentCancelAction $cancelAction */
        $cancelAction = $dibs->get('dibs.action.payment_cancel');

        $result = $cancelAction->cancelOrderPayments($idOrders);
        if (is_array($result)) {
            $this->confirmations[] = $this->l('Payments successfully canceled');
            $this->errors[] =
                sprintf($this->l('Failed to cancel payment for orders with ID of %s'), implode(',', $result));
            return;
        } elseif (false == $result) {
            $this->errors[] = $this->l('Failed to cancel all payments');
            return;
        }

        $this->confirmations[] = $this->l('Payments successfully canceled');
    }

    public function processBulkRefundPayments()
    {
        $orders = Tools::getValue('orderBox');

        /** @var Dibs $dibs */
        $dibs = Module::getInstanceByName('dibs');
        /** @var \Invertus\Dibs\Action\PaymentRefundAction $refundAction */
        $refundAction = $dibs->get('dibs.action.payment_refund');

        $result = $refundAction->refundPayments($orders);
        if (is_array($result)) {
            $this->confirmations[] = $this->l('Payments successfully refunded');
            $this->errors[] =
                sprintf($this->l('Failed to refund payment for orders with ID of %s'), implode(',', $result));
            return;
        } elseif (false == $result) {
            $this->errors[] = $this->l('Failed to refund all payments');
            return;
        }

        $this->confirmations[] = $this->l('Payments successfully refunded');
    }

    public function processCancelPayment()
    {
        $idOrder = Tools::getValue('id_order');
        $order = new Order($idOrder);

        /** @var Dibs $dibs */
        $dibs = Module::getInstanceByName('dibs');
        /** @var \Invertus\Dibs\Action\PaymentCancelAction $cancelAction */
        $cancelAction = $dibs->get('dibs.action.payment_cancel');

        $success = $cancelAction->cancelOrderPayment($order);
        if (!$success) {
            $this->errors[] = $this->l('Failed to cancel payment');
            return;
        }

        $this->confirmations[] = $this->l('Payment successfully canceled');
    }

    public function processChargePayment()
    {
        $idOrder = Tools::getValue('id_order');
        $order = new Order($idOrder);

        /** @var Dibs $dibs */
        $dibs = Module::getInstanceByName('dibs');
        /** @var \Invertus\Dibs\Action\PaymentChargeAction $chargeAction */
        $chargeAction = $dibs->get('dibs.action.payment_charge');

        $success = $chargeAction->chargePayment($order);
        if (!$success) {
            $this->errors[] = $this->l('Failed to charge payment');
            return;
        }

        $this->confirmations[] = $this->l('Payment successfully charged');
    }

    public function processRefundPayment()
    {
        $idOrder = Tools::getValue('id_order');
        $order = new Order($idOrder);

        /** @var Dibs $dibs */
        $dibs = Module::getInstanceByName('dibs');
        /** @var \Invertus\Dibs\Action\PaymentRefundAction $refundAction */
        $refundAction = $dibs->get('dibs.action.payment_refund');

        $success = $refundAction->refundPayment($order);
        if (!$success) {
            $this->errors[] = $this->l('Failed to refund payment');
            return;
        }

        $this->confirmations[] = $this->l('Payment successfully refunded');
    }

    public function displayChargePaymentLink($token, $idOrder)
    {
        unset($token);
        $orderPayment = $this->getOrderPayment($idOrder);
        if (!$orderPayment || !$orderPayment->canBeCharged()) {
            return null;
        }

        $params = array(
            'href' => self::$currentIndex.'&action=chargePayment&token='.$this->token.'&id_order='.(int)$idOrder,
            'action' => $this->l('Charge Payment'),
        );

        return $this->renderListAction($params);
    }

    public function displayCancelPaymentLink($token, $idOrder)
    {
        unset($token);
        $orderPayment = $this->getOrderPayment($idOrder);
        if (!$orderPayment || !$orderPayment->canBeCanceled()) {
            return null;
        }

        $params = array(
            'href' => self::$currentIndex.'&action=cancelPayment&token='.$this->token.'&id_order='.(int)$idOrder,
            'action' => $this->l('Cancel Payment'),
        );

        return $this->renderListAction($params);
    }

    public function displayRefundPaymentLink($token, $idOrder)
    {
        unset($token);
        $orderPayment = $this->getOrderPayment($idOrder);
        if (!$orderPayment || !$orderPayment->canBeRefunded()) {
            return null;
        }

        $params = array(
            'href' => self::$currentIndex.'&action=refundPayment&token='.$this->token.'&id_order='.(int)$idOrder,
            'action' => $this->l('Refund Payment'),
        );

        return $this->renderListAction($params);
    }

    private function renderListAction(array $params)
    {
        $dibs = Module::getInstanceByName('dibs');

        $this->context->smarty->assign($params);

        return $this->context->smarty->fetch($dibs->getLocalPath().'views/templates/admin/list-action.tpl');
    }

    private function getOrderPayment($idOrder)
    {
        /** @var Dibs $dibs */
        $dibs = Module::getInstanceByName('dibs');
        /** @var \Invertus\Dibs\Repository\OrderPaymentRepository $orderPaymentRepository */
        $orderPaymentRepository = $dibs->get('dibs.repository.order_payment');

        return $orderPaymentRepository->findOrderPaymentByOrderId($idOrder);
    }
}
