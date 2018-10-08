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

class DibsEasyValidationModuleFrontController extends ModuleFrontController
{
    const FILENAME = 'validation';

    /**
     * @var DibsEasy
     */
    public $module;

    /**
     * Check if customer can access this page
     */
    public function checkAccess()
    {
        $cart = $this->context->cart;
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
        $idCart = $this->context->cart->id;

        // Get payment which is associated with cart
        // It's simple mapping (id_cart - id_order - id_payment (dibs) - id_charge (dibs) - etc.)
        /** @var \Invertus\DibsEasy\Repository\OrderPaymentRepository $orderPaymentRepository */
        $orderPaymentRepository = $this->module->get('dibs.repository.order_payment');
        $orderPayment = $orderPaymentRepository->findOrderPaymentByCartId($idCart);
        if (!$orderPayment instanceof DibsOrderPayment) {
            $this->addFlash('error', $this->module->l('Unexpected error occured.', self::FILENAME));
            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'checkout'));
        }

        // Before creating order let's make some validations
        // First let's check if paid amount and currency is the same as it is in cart
        $payment = $this->validateCartPayment($orderPayment->id_payment);
        if (false == $payment) {
            $this->cancelCartPayment();
            $this->addFlash(
                'error',
                $this->module->l('Payment validation has failed. Payment was canceled.', self::FILENAME)
            );
            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'checkout'));
        }

        // Update payment mapping to be reserved
        $orderPayment->is_reserved = 1;
        $orderPayment->update();

        // Then check if payment country is valid
        if (!$this->validatePaymentCountry($payment)) {
            $this->cancelCartPayment();
            $this->addFlash('error', $this->module->l('Payment was canceled due to invalid country.', self::FILENAME));
            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'checkout'));
        }

        // If validations passed, let do some processing before creating order
        // First assign customer to cart if it does not exist
        if (!$this->processSaveCartCustomer($payment)) {
            $this->cancelCartPayment();
            $this->addFlash('error', implode('. ', $this->errors));
            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'checkout'));
        }

        // After processing is done, let's create order
        try {
            $idOrderState = (int) Configuration::get('DIBS_ACCEPTED_ORDER_STATE_ID');

            $extraTplVars = array();
            if (!$this->module->isPS16()) {
                $extraTplVars =
                    $this->module->getExtraTemplateVars($idCart, $this->context->cart->id_carrier, $idOrderState);
            }

            $this->module->validateOrder(
                $idCart,
                $idOrderState,
                $this->context->cart->getOrderTotal(),
                $this->module->displayName,
                null,
                $extraTplVars,
                $this->context->currency->id,
                false,
                $this->context->cart->secure_key
            );
        } catch (Exception $e) {
            // If we were unable to create order then cancel payment and redirect back to checkout
            /** @var \Invertus\DibsEasy\Action\PaymentCancelAction $paymentCancelAction */
            $paymentCancelAction = $this->module->get('dibs.action.payment_cancel');
            $paymentCancelAction->cancelCartPayment($this->context->cart);

            $error =$this->module->l('Payment was canceled due to order creation failure.', self::FILENAME);
            $this->addFlash('error', $error);

            Tools::redirect($this->context->link->getModuleLink($this->module->name, 'checkout'));
        }

        $idOrder = Order::getOrderByCartId($this->context->cart->id);
        $order = new Order($idOrder);

        // Update payment mappings
        $orderPayment->is_reserved = 1;
        $orderPayment->id_order = $order->id;
        $orderPayment->save();

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
     * @param \Invertus\DibsEasy\Result\Payment $payment
     *
     * @return bool
     */
    protected function processSaveCartCustomer(\Invertus\DibsEasy\Result\Payment $payment)
    {
        $customer = new Customer($this->context->cart->id_customer);
        if (Validate::isLoadedObject($customer)) {
            return true;
        }

        $person = $payment->getConsumer()->getPrivatePerson();

        $company = $payment->getConsumer()->getCompany();
        $firstName = $person->getFirstName() ?: $company->getFirstName();
        $lastName = $person->getLastName() ?: $company->getLastName();
        $email = $person->getEmail() ?: $company->getEmail();

        $idCustomer = Customer::customerExists($email, true, false);
        if ($idCustomer) {
            $errorMessage = $this->module->l(
                'Payment was canceled, because customer with email %s was found, please sign in.',
                self::FILENAME
            );
            $this->errors[] = sprintf($errorMessage, $email);
            return false;
        }

        $newPassword = Tools::passwdGen();

        $customer = new Customer();
        $customer->firstname = $firstName;
        $customer->lastname = $lastName;
        $customer->email = $email;
        $customer->passwd = Tools::encrypt($newPassword);
        $customer->is_guest = 0;
        $customer->id_default_group = Configuration::get('PS_CUSTOMER_GROUP', null, $this->context->cart->id_shop);
        $customer->newsletter = 0;
        $customer->optin = 0;
        $customer->active = 1;
        $customer->id_gender = 9;
        
        if ($errors = $customer->validateController()) {
            $this->errors = array_merge($this->errors, $errors);
            return false;
        }
        
        $customer->save();

        $this->sendConfirmationEmail($customer, $newPassword);

        $this->processLogin($customer);

        $this->context->cart->id_customer = $customer->id;
        $this->context->cart->secure_key = $customer->secure_key;

        if (!$this->context->cart->save()) {
            $this->errors[] = $this->module->l(
                'Payment was canceled, because customer account could not be saved.',
                self::FILENAME
            );
            
            return false;
        }
        
        return true;
    }

    /**
     * Validate if cart payment has been reserved.
     *
     * @param string $paymentId
     *
     * @return bool|\Invertus\DibsEasy\Result\Payment
     */
    protected function validateCartPayment($paymentId)
    {
        /** @var \Invertus\DibsEasy\Action\PaymentGetAction $paymentGetAction */
        $paymentGetAction = $this->module->get('dibs.action.payment_get');
        $payment = $paymentGetAction->getPayment($paymentId);

        if (null == $payment) {
            return false;
        }

        $cartCurrency = new Currency($this->context->cart->id_currency);
        $cartAmount = (int) (string) ($this->context->cart->getOrderTotal() * 100);

        $summary = $payment->getSummary();
        $orderDetail = $payment->getOrderDetail();

        if ($summary->getReservedAmount() != $cartAmount ||
            $orderDetail->getCurrency() != $cartCurrency->iso_code
        ) {
            return false;
        }

        return $payment;
    }

    /**
     * Validate if payment country is valid
     *
     * @param \Invertus\DibsEasy\Result\Payment $payment
     *
     * @return bool
     */
    protected function validatePaymentCountry(\Invertus\DibsEasy\Result\Payment $payment)
    {
        $country = $payment->getConsumer()
            ->getShippingAddress()
            ->getCountry();

        $alpha2IsoCode = $this->getAlpha2FromAlpha3CountryIso($country);

        return null !== $alpha2IsoCode;
    }

    /**
     * Get coutnry ISO Alpha2 from Country ISO Alpha3
     *
     * @param string $alpha3Iso
     *
     * @return null|string
     */
    protected function getAlpha2FromAlpha3CountryIso($alpha3Iso)
    {
        /** @var \Invertus\DibsEasy\Service\CountryMapper $countryMapper */
        $countryMapper = $this->module->get('dibs.service.country_mapper');
        $mappings = $countryMapper->mappings();

        $alpha2Iso = null;

        if (isset($mappings[$alpha3Iso])) {
            $alpha2Iso = $mappings[$alpha3Iso];
        }

        return $alpha2Iso;
    }

    /**
     * Process customer login
     *
     * @param Customer $customer
     */
    protected function processLogin(Customer $customer)
    {
        $this->context->cookie->id_compare = isset($this->context->cookie->id_compare) ?
            $this->context->cookie->id_compare :
            CompareProduct::getIdCompareByIdCustomer($customer->id);
        $this->context->cookie->id_customer = (int)($customer->id);
        $this->context->cookie->customer_lastname = $customer->lastname;
        $this->context->cookie->customer_firstname = $customer->firstname;
        $this->context->cookie->logged = 1;
        $customer->logged = 1;
        $this->context->cookie->is_guest = $customer->isGuest();
        $this->context->cookie->passwd = $customer->passwd;
        $this->context->cookie->email = $customer->email;

        // Add customer to the context
        $this->context->customer = $customer;

        $this->context->cookie->write();

        Hook::exec('actionAuthentication', array('customer' => $this->context->customer));

        // Login information have changed, so we check if the cart rules still apply
        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);
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

    /**
     * Send welcome email if new customer is created
     *
     * @param Customer $customer
     * @param string $password
     *
     * @return bool|int
     */
    private function sendConfirmationEmail(Customer $customer, $password)
    {
        if (!Configuration::get('PS_CUSTOMER_CREATION_EMAIL')) {
            return true;
        }

        return Mail::Send(
            $this->context->language->id,
            'account',
            Mail::l('Welcome!'),
            array(
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{email}' => $customer->email,
                '{passwd}' => $password
            ),
            $customer->email,
            $customer->firstname.' '.$customer->lastname
        );
    }
}
