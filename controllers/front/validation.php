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

/**
 * Validates DIBS Easy payment and creates order
 */
class DibsEasyValidationModuleFrontController extends ModuleFrontController
{


    /**
     * @var DibsEasy
     */
    public $module;

    /**
     * Check if customer can access this page
     */
    public function checkAccess()
    {

        $cart = new Cart(Tools::getValue('id'));

        // if order was placed with
        // webhook we redirect to place order success page

        if($cart->orderExists()) {

            $id_order = Order::getOrderByCartId((int)$cart->id);
            $order = new Order($id_order );

            if(!$this->context->customer->id) {
                $customer = new Customer($order->getCustomer()->id);
                $this->module->processLogin($customer);
            }

            $orderConfirmationUrl = $this->context->link->getPageLink(
                'order-confirmation',
                true,
                $this->context->language->id,
                array(
                    'id_cart' => $cart->id,
                    'id_module' => $this->module->id,
                    'id_order' => $id_order,
                    'key' => $order->getCustomer()->secure_key,
                )
            );

            Tools::redirect($orderConfirmationUrl);
        }

        $customer = $this->context->customer;

        if (!Validate::isLoadedObject($cart) ||
            (int) $cart->id_customer != (int) $customer->id ||
            !$this->module->isConfigured() ||
            !$this->module->active ||
            $cart->orderExists()
        ) {
            $this->cancelCartPayment();
            Tools::redirect('index.php?controller=order&step=1');
        }

        $guestCheckoutEnabled = (bool) Configuration::get('PS_GUEST_CHECKOUT_ENABLED');
        if (!$guestCheckoutEnabled && !$this->context->customer->isLogged()) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        return true;
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        try {
            $order = $this->module->placeOrder($this->context->cart->id);
        }catch (Exception $ex) {

             $this->addFlash(
                'error',
                $ex->getMessage()
            );

            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'checkout'));
        }

        // After processing is done, there's one more thing to do
        // Redirect to order confirmation page
        $orderConfirmationUrl = $this->context->link->getPageLink(
            'order-confirmation',
            true,
            $this->context->language->id,
            array(
                'id_cart' => $order->id_cart,
                'id_module' => $this->module->id,
                'id_order' => $order->id,
                'key' => $order->getCustomer()->secure_key,
            )
        );
        Tools::redirect($orderConfirmationUrl);
    }

    /**
     * Cancel any payment that has been reserved
     *
     * @return bool
     */
    protected function cancelCartPayment()
    {
        if (!Validate::isLoadedObject($this->context->cart)) {
            return true;
        }

        /** @var \Invertus\DibsEasy\Action\PaymentCancelAction $paymentCancelAction */
        $paymentCancelAction = $this->module->get('dibs.action.payment_cancel');

        return $paymentCancelAction->cancelCartPayment($this->context->cart);
    }

    /**
     * Add flash message
     *
     * @param string $type Can be success, error & etc.
     * @param string $message
     */
    protected function addFlash($type, $message)
    {
        $this->context->cookie->{$type} = $message;
    }
}
