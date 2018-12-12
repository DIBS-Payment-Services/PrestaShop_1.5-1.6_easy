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

class DibsEasyCheckoutModuleFrontController extends ModuleFrontController
{
    const CHANGE_DELIVERY_OPTION_ACTION = 'changeDeliveryOption';
    const ADD_DISCOUNT_ACTION = 'addDiscount';

    /**
     * @var DibsEasy
     */
    public $module;

    /**
     * @var bool
     */
    public $ssl = true;

    /**
     * @var array These variables are passed to JS
     */
    protected $jsVariables = array();

    /**
     * Initialize variables/constants for PS 1.5 compatability
     *
     * @todo: remove in PS 1.7
     */
    public function init()
    {
        parent::init();

        if (!defined('_PS_PRICE_COMPUTE_PRECISION_')) {
            define('_PS_PRICE_COMPUTE_PRECISION_', Configuration::get('PS_PRICE_DISPLAY_PRECISION'));
        }
    }

    /**
     * Check if customer can access checkout page.
     *
     * @retun bool
     */
    public function checkAccess()
    {
        // If guest checkout is enabled and customer is not logged in, then redirect to standard checkout
        $guestCheckoutEnabled = (bool) Configuration::get('PS_GUEST_CHECKOUT_ENABLED');
        if (!$guestCheckoutEnabled && !$this->context->customer->isLogged()) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // General checks
        if (!$this->module->active ||
            !$this->module->isConfigured()
        ) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // If cart is not initialized or cart is empty redirect to default cart page
        if (!isset($this->context->cart) || $this->context->cart->nbProducts() <= 0) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = new Currency($this->context->cart->id_currency);
        $supportedCurrencies = $this->module->getParameter('supported_currencies');

        // If currency is not supported then redirect to default checkout
        if (!in_array($currency->iso_code, $supportedCurrencies)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // if there are no supported countries
        // then redirect to default checkout
        if (!$this->module->get('dibs.service.default_shipping_country_provider')->anyAvailableCountries()) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        return true;
    }

    /**
     * Add custom JS & CSS to controller
     */
    public function setMedia()
    {
        parent::setMedia();

        $isTestingModeOn = (bool) Configuration::get('DIBS_TEST_MODE');
        switch ($isTestingModeOn) {
            case false:
                $checkoutJs = $this->module->getParameter('js_checkout_prod_url');
                $checkoutKey = Configuration::get('DIBS_PROD_CHECKOUT_KEY');
                break;
            default:
            case true:
                $checkoutJs = $this->module->getParameter('js_checkout_test_url');
                $checkoutKey = Configuration::get('DIBS_TEST_CHECKOUT_KEY');
                break;
        }

        $this->context->smarty->assign('DIBS_TEST_MODE', $isTestingModeOn);
        $language = Configuration::get('DIBS_LANGUAGE');

        $checkoutUrl = $this->context->link->getModuleLink($this->module->name, 'checkout');
        $validationUrl = $this->context->link->getModuleLink($this->module->name, 'validation');

        $this->jsVariables['dibsCheckout']['checkoutKey'] = $checkoutKey;
        $this->jsVariables['dibsCheckout']['language'] = $language;
        $this->jsVariables['dibsCheckout']['validationUrl'] = $validationUrl;
        $this->jsVariables['dibsCheckout']['checkoutUrl'] = $checkoutUrl;
        $this->jsVariables['dibsCheckout']['actions']['changeDeliveryOption'] = self::CHANGE_DELIVERY_OPTION_ACTION;
        $this->jsVariables['dibsCheckout']['actions']['addDiscount'] = self::ADD_DISCOUNT_ACTION;
        $this->jsVariables['dibsCheckout']['addressUrl'] = $this->context->link->getModuleLink(
            $this->module->name,
            'address'
        );

        $this->addJqueryPlugin('fancybox');
        $this->context->controller->addCSS($this->module->getPathUri().'views/css/checkout.css');
        $this->context->controller->addJS($checkoutJs);
        $this->context->controller->addJS($this->module->getPathUri().'views/js/checkout.js');
    }

    /**
     * Process actions
     */
    public function postProcess()
    {
        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);

        $this->assignAddressToCart();
        $this->assignCarrierToCart();

        $action = Tools::getValue('action');

        switch ($action) {
            case self::CHANGE_DELIVERY_OPTION_ACTION:
                $this->processDeliveryOptionChange();
                break;
            case self::ADD_DISCOUNT_ACTION:
                $this->processAddDiscount();
                break;
        }

        $orderPayment = $this->getOrderPayment();

        $this->jsVariables['dibsCheckout']['paymentID'] = $orderPayment->id_payment;
        $this->jsVariables['dibsCheckout']['refreshUrl'] = $this->context->link->getModuleLink(
            $this->module->name,
            'checkout',
            [
                'paymentId' => $orderPayment->id_payment,
            ]
        );
    }

    /**
     * Initialize header
     */
    public function initHeader()
    {
        parent::initHeader();

        if ($this->module->isPS16()) {
            Media::addJsDef($this->jsVariables);
        } else {
            // These variables will be fetched in initContent method.
            $this->context->smarty->assign('dibsCheckout', $this->jsVariables['dibsCheckout']);
        }
    }

    /**
     * Initialize checkout content
     */
    public function initContent()
    {
        parent::initContent();

        if (!$this->module->isPS16()) {
            $hookHeaderVariable = $this->context->smarty->getVariable('HOOK_HEADER');
            $this->context->smarty->assign(
                'HOOK_HEADER',
                $hookHeaderVariable->value.$this->context->smarty->fetch(
                    $this->module->getLocalPath().'views/templates/front/js.tpl'
                )
            );

            $this->context->smarty->assign(array(
                'is_guest' => $this->context->customer->is_guest,
                'currencySign' => $this->context->currency->sign,
                'currencyRate' => $this->context->currency->conversion_rate,
                'currencyFormat' => $this->context->currency->format,
                'currencyBlank' => $this->context->currency->blank,
            ));
        }

        $this->assignSummaryInformations();
        $this->assignWrappingAndTOS();
        $this->assignDeliveryOptions();
        $this->assignFlashMessages();

        $idLang = $this->context->language->id;
        $this->context->smarty->assign(array(
            'regularCheckoutUrl' => $this->context->link->getPageLink('order', true, $idLang, array('step' => 1)),
        ));

        $this->setTemplate('checkout.tpl');
    }

    /**
     * Add discount to cart
     */
    protected function processAddDiscount()
    {
        if (!CartRule::isFeatureActive()) {
            return;
        }

        $code = trim(Tools::getValue('discount_name'));
        if (!$code || !Validate::isCleanHtml($code)) {
            return;
        }

        $cartRule = new CartRule(CartRule::getIdByCode($code));
        if (!Validate::isLoadedObject($cartRule)) {
            return;
        }

        if ($cartRule->checkValidity($this->context, false, true)) {
            return;
        }

        $this->context->cart->addCartRule($cartRule->id);

        CartRule::autoAddToCart($this->context);
    }

    /**
     * Process delivery option change in cart.
     */
    protected function processDeliveryOptionChange()
    {
        $selectedDeliveryOption = Tools::getValue('delivery_option');

        if (!$this->validateDeliveryOption($selectedDeliveryOption)) {
            return;
        }

        $this->context->cart->setDeliveryOption($selectedDeliveryOption);
        $this->context->cart->update();

        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);
    }

    /**
     * Assign DIBS flash messages
     */
    protected function assignFlashMessages()
    {
        if (isset($this->context->cookie->error)) {
            $this->context->smarty->assign('dibsError', $this->context->cookie->error);
            unset($this->context->cookie->error);
        }
    }

    /**
     * Check if delivery option is valid value
     *
     * @param array $deliveryOption
     *
     * @return bool
     */
    protected function validateDeliveryOption($deliveryOption)
    {
        if (!is_array($deliveryOption)) {
            return false;
        }

        foreach ($deliveryOption as $option) {
            if (!preg_match('/(\d+,)?\d+/', $option)) {
                return false;
            }
        }

        return true;
    }

    protected function getDeliveryAddressId()
    {
        $idAddress = null;

        switch ($this->context->currency->iso_code) {
            case 'DKK':
                $idAddress = Configuration::get('DIBS_DENMARK_ADDRESS_ID');
                break;
            case 'NOK':
                $idAddress = Configuration::get('DIBS_NORWAY_ADDRESS_ID');
                break;
            case 'SEK':
            default:
                $idAddress = Configuration::get('DIBS_SWEEDEN_ADDRESS_ID');
                break;
        }

        return (int) $idAddress;
    }

    protected function getOrderPayment()
    {
        /** @var \Invertus\DibsEasy\Repository\OrderPaymentRepository $orderPaymentRepository */
        $orderPaymentRepository = $this->module->get('dibs.repository.order_payment');
        $orderPayment = $orderPaymentRepository->findOrderPaymentByCartId($this->context->cart->id);
        if ($orderPayment) {
            $orderPayment->delete();
        }

        if (Tools::isSubmit('paymentId')) {
            $paymentId = Tools::getValue('paymentId');

            /** @var \Invertus\DibsEasy\Action\PaymentGetAction $paymentGetAction */
            $paymentGetAction = $this->module->get('dibs.action.payment_get');
            $payment = $paymentGetAction->getPayment($paymentId);

            if (null === $payment) {
                Tools::redirect($this->context->link->getModuleLink($this->module->name, 'checkout'));
            }

            $paymentAmountInCents = $payment->getOrderDetail()->getAmount();
            $cartAmountInCents = \Invertus\DibsEasy\Service\PriceToCentsConverter::convert(
                $this->context->cart->getOrderTotal()
            );

            $paymentCurrency = $payment->getOrderDetail()->getCurrency();
            $cartCurrency = new Currency($this->context->cart->id_currency);

            if ($cartCurrency->iso_code !== $paymentCurrency) {
                // If payment currency has changed
                // Then skip and redirect to checkout without payment id
                // To create new payment with valid details
                Tools::redirect($this->context->link->getModuleLink($this->module->name, 'checkout'));
            }

            if ($paymentAmountInCents !== $cartAmountInCents) {
                // If payment id is in url
                // and cart amount does not equal payment amount
                // then it means shipping cost has (probably) changed
                // so we attempt to update payment items.
                /** @var \Invertus\DibsEasy\Action\PaymentUpdateCartItemsAction $updateCartItemsAction */
                $updateCartItemsAction = $this->module->get('dibs.action.payment_update_items');
                $hasUpdated = $updateCartItemsAction->updatePaymentItems(
                    $paymentId,
                    $this->context->cart
                );
                if (!$hasUpdated) {
                    // if update failed
                    // then redirect to checkout without payment id
                    // to initialize new payment
                    Tools::redirect($this->context->link->getModuleLink($this->module->name, 'checkout'));
                }
            }

            $orderPayment = new DibsOrderPayment();
            $orderPayment->id_payment = $paymentId;
            $orderPayment->id_cart = $this->context->cart->id;

            if ($orderPayment->save()) {
                // update checkout url in JS variable to have paymentId
                $this->jsVariables['dibsCheckout']['checkoutUrl'] = $this->context->link->getModuleLink(
                    $this->module->name,
                    'checkout',
                    [
                        'paymentId' => $paymentId,
                    ]
                );

                return $orderPayment;
            }
        }

        /** @var \Invertus\DibsEasy\Action\PaymentCreateAction $paymentCreateAction */
        $paymentCreateAction = $this->module->get('dibs.action.payment_create');
        $orderPayment = $paymentCreateAction->createPayment($this->context->cart);

        if (false === $orderPayment) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        return $orderPayment;
    }

    /**
     * Assigns carrier to cart, so it always exists
     */
    protected function assignCarrierToCart()
    {
        $carrier = new Carrier($this->context->cart->id_carrier);

        if (Validate::isLoadedObject($carrier)) {
            // if carrier is not deleted
            // then it's okay to use it
            if (!$carrier->deleted) {
                return;
            }

            if ($carrier->active) {
                // if carrier is deleted, lets try using updated carrier
                $carrier = Carrier::getCarrierByReference($carrier->id_reference);

                // if updated carrier exists
                // then update cart data and use it
                if (false !== $carrier) {
                    $option = [$this->context->cart->id_address_delivery => $carrier->id.','];
                    $this->context->cart->setDeliveryOption($option);
                    $this->context->cart->update();

                    return;
                }
            }
        }

        // in case carrier was deleted or not set yet
        // let use first carrier available
        $address = new Address($this->context->cart->id_address_delivery);
        $deliveryOptions = $this->context->cart->getDeliveryOptionList(new Country($address->id_country));

        if (isset($deliveryOptions[$address->id]) &&
            is_array($deliveryOptions[$address->id])
        ) {
            reset($deliveryOptions[$address->id]);
            $carrierIdWithComma = key($deliveryOptions[$address->id]);
            $option = [$this->context->cart->id_address_delivery => $carrierIdWithComma];
            $this->context->cart->setDeliveryOption($option);
            $this->context->cart->update();
            return;
        }

        // last but not least
        // fallback to default carrier
        $idCarrierDefault = (int) Configuration::get('PS_CARRIER_DEFAULT');
        $option = [$this->context->cart->id_address_delivery => $idCarrierDefault.','];
        $this->context->cart->setDeliveryOption($option);
        $this->context->cart->update();
    }

    /**
     * Assign customer's address to cart
     */
    private function assignAddressToCart()
    {
        if (!$this->context->cart->id_address_delivery) {
            $this->context->cart->id_address_delivery = $this->getDeliveryAddressId();
            $this->context->cart->save();
        }
    }

    /**
     * This fucntion is copy/paste from ParentOrderControllerCore.
     * It assigns wrapping and TOS variables to smarty.
     *
     * @see ParentOrderControllerCore::_assignCarrier()
     */
    protected function assignDeliveryOptions()
    {
        $idCountrySweden = Country::getByIso('SE');
        $country = new Country($idCountrySweden);

        if ($this->context->cart && $this->context->cart->id_address_delivery) {
            $deliveryAddress = new Address($this->context->cart->id_address_delivery);

            if ($deliveryAddress->id_country) {
                $country = new Country($deliveryAddress->id_country);
            }
        }

        $carriers = $this->context->cart->simulateCarriersOutput(null, true);
        $checked = $this->context->cart->simulateCarrierSelectedOutput(false);
        $delivery_option_list = $this->context->cart->getDeliveryOptionList($country);
        $delivery_option = $this->context->cart->getDeliveryOption($country, false);

        if (!$this->context->cart->getDeliveryOption(null, true)) {
            $this->context->cart->setDeliveryOption($this->context->cart->getDeliveryOption());
        }

        $this->context->smarty->assign(array(
            'address_collection' => $this->context->cart->getAddressCollection(),
            'delivery_option_list' => $delivery_option_list,
            'carriers' => $carriers,
            'checked' => $checked,
            'delivery_option' => $delivery_option,
            'multi_shipping' => (bool) Tools::getValue('multi-shipping'),
        ));

        $advanced_payment_api = (bool) Configuration::get('PS_ADVANCED_PAYMENT_API');

        $vars = array(
            'HOOK_BEFORECARRIER' => Hook::exec('displayBeforeCarrier', array(
                'carriers' => $carriers,
                'checked' => $checked,
                'delivery_option_list' => $delivery_option_list,
                'delivery_option' => $delivery_option,
            )),
            'advanced_payment_api' => $advanced_payment_api,
        );

        Cart::addExtraCarriers($vars);

        $this->context->smarty->assign($vars);
    }

    /**
     * This function is copy/paste from ParentOrderControllerCore.
     * It assigns wrapping and TOS variables to smarty.
     *
     * @see ParentOrderControllerCore::_assignWrappingAndTOS()
     */
    protected function assignWrappingAndTOS()
    {
        // Wrapping fees
        $wrapping_fees = $this->context->cart->getGiftWrappingPrice(false);
        $wrapping_fees_tax_inc = $this->context->cart->getGiftWrappingPrice();

        $free_shipping = false;
        foreach ($this->context->cart->getCartRules() as $rule) {
            if ($rule['free_shipping'] && !$rule['carrier_restriction']) {
                $free_shipping = true;
                break;
            }
        }
        $this->context->smarty->assign(array(
            'free_shipping' => $free_shipping,
            'checkedTOS' => (int)$this->context->cookie->checkedTOS,
            'recyclablePackAllowed' => (int)Configuration::get('PS_RECYCLABLE_PACK'),
            'giftAllowed' => (int)Configuration::get('PS_GIFT_WRAPPING'),
            'cms_id' => (int)Configuration::get('PS_CONDITIONS_CMS_ID'),
            'conditions' => (int)Configuration::get('PS_CONDITIONS'),
            'link_conditions' => '',
            'recyclable' => (int)$this->context->cart->recyclable,
            'delivery_option_list' => $this->context->cart->getDeliveryOptionList(),
            'carriers' => $this->context->cart->simulateCarriersOutput(),
            'checked' => $this->context->cart->simulateCarrierSelectedOutput(),
            'address_collection' => $this->context->cart->getAddressCollection(),
            'delivery_option' => $this->context->cart->getDeliveryOption(null, false),
            'gift_wrapping_price' => (float)$wrapping_fees,
            'total_wrapping_cost' => Tools::convertPrice($wrapping_fees_tax_inc, $this->context->currency),
            'override_tos_display' => Hook::exec('overrideTOSDisplay'),
            'total_wrapping_tax_exc_cost' => Tools::convertPrice($wrapping_fees, $this->context->currency)
        ));
    }

    /**
     * This function is copy/paste from ParentOrderControllerCore.
     * It assigns cart summary variables to smarty.
     *
     * @see ParentOrderControllerCore::_assignSummaryInformations()
     */
    protected function assignSummaryInformations()
    {
        $summary = $this->context->cart->getSummaryDetails();
        $customizedDatas = Product::getAllCustomizedDatas($this->context->cart->id);

        // override customization tax rate with real tax (tax rules)
        if ($customizedDatas) {
            foreach ($summary['products'] as &$productUpdate) {
                $productId = (int)isset($productUpdate['id_product']) ?
                    $productUpdate['id_product'] :
                    $productUpdate['product_id'];
                $productAttributeId = (int)isset($productUpdate['id_product_attribute']) ?
                    $productUpdate['id_product_attribute'] :
                    $productUpdate['product_attribute_id'];

                if (isset($customizedDatas[$productId][$productAttributeId])) {
                    $productUpdate['tax_rate'] = Tax::getProductTaxRate(
                        $productId,
                        $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}
                    );
                }
            }

            Product::addCustomizationPrice($summary['products'], $customizedDatas);
        }

        $null = null;
        $cart_product_context = Context::getContext()->cloneContext();
        foreach ($summary['products'] as $key => &$product) {
            $product['quantity'] = $product['cart_quantity'];// for compatibility with 1.2 themes

            if ($cart_product_context->shop->id != $product['id_shop']) {
                $cart_product_context->shop = new Shop((int)$product['id_shop']);
            }
            $product['price_without_specific_price'] = Product::getPriceStatic(
                $product['id_product'],
                !Product::getTaxCalculationMethod(),
                $product['id_product_attribute'],
                6,
                null,
                false,
                false,
                1,
                false,
                null,
                null,
                null,
                $null,
                true,
                true,
                $cart_product_context
            );

            if (Product::getTaxCalculationMethod()) {
                $product['is_discounted'] =
                    Tools::ps_round($product['price_without_specific_price'], _PS_PRICE_COMPUTE_PRECISION_) !=
                    Tools::ps_round($product['price'], _PS_PRICE_COMPUTE_PRECISION_);
            } else {
                $product['is_discounted'] =
                    Tools::ps_round($product['price_without_specific_price'], _PS_PRICE_COMPUTE_PRECISION_) !=
                    Tools::ps_round($product['price_wt'], _PS_PRICE_COMPUTE_PRECISION_);
            }
        }

        // Get available cart rules and unset the cart rules already in the cart
        $id_customer = isset($this->context->customer->id) ? $this->context->customer->id : 0;
        $available_cart_rules = CartRule::getCustomerCartRules(
            $this->context->language->id,
            $id_customer,
            true,
            true,
            true,
            $this->context->cart,
            false,
            true
        );
        $cart_cart_rules = $this->context->cart->getCartRules();
        foreach ($available_cart_rules as $key => $available_cart_rule) {
            foreach ($cart_cart_rules as $cart_cart_rule) {
                if ($available_cart_rule['id_cart_rule'] == $cart_cart_rule['id_cart_rule']) {
                    unset($available_cart_rules[$key]);
                    continue 2;
                }
            }
        }

        $show_option_allow_separate_package =
            (!$this->context->cart->isAllProductsInStock(true) && Configuration::get('PS_SHIP_WHEN_AVAILABLE'));
        $advanced_payment_api = (bool)Configuration::get('PS_ADVANCED_PAYMENT_API');

        $this->context->smarty->assign($summary);
        $this->context->smarty->assign(array(
            'token_cart' => Tools::getToken(false),
            'isLogged' => $this->context->customer->isLogged(),
            'isVirtualCart' => $this->context->cart->isVirtualCart(),
            'productNumber' => $this->context->cart->nbProducts(),
            'voucherAllowed' => CartRule::isFeatureActive(),
            'shippingCost' => $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING),
            'shippingCostTaxExc' => $this->context->cart->getOrderTotal(false, Cart::ONLY_SHIPPING),
            'customizedDatas' => $customizedDatas,
            'CUSTOMIZE_FILE' => Product::CUSTOMIZE_FILE,
            'CUSTOMIZE_TEXTFIELD' => Product::CUSTOMIZE_TEXTFIELD,
            'lastProductAdded' => $this->context->cart->getLastProduct(),
            'displayVouchers' => $available_cart_rules,
            'show_option_allow_separate_package' => $show_option_allow_separate_package,
            'smallSize' => Image::getSize(ImageType::getFormatedName('small')),
            'advanced_payment_api' => $advanced_payment_api,
            'back' => '',
        ));

        $this->context->smarty->assign(array(
            'HOOK_SHOPPING_CART' => Hook::exec('displayShoppingCartFooter', $summary),
            'HOOK_SHOPPING_CART_EXTRA' => Hook::exec('displayShoppingCart', $summary)
        ));
    }
}
