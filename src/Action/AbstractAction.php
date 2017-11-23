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

use Address;
use Carrier;
use Cart;
use Invertus\DibsEasy\Adapter\PriceRoundAdapter;
use Invertus\DibsEasy\Payment\PaymentItem;
use Invertus\DibsEasy\Util\NameNormalizer;
use Order;

/**
 * Class AbstractAction
 *
 * @package Invertus\DibsEasy\Action
 */
abstract class AbstractAction
{
    /**
     * Module instance used for translations
     *
     * @return \DibsEasy
     */
    abstract protected function getModule();

    /**
     * Get cart items for payment
     *
     * @param Cart $cart
     *
     * @return array|PaymentItem[]
     */
    protected function getCartProductItems(Cart $cart)
    {
        $idCurrency = $cart->id_currency;
        $priceRounder = new PriceRoundAdapter();
        $nameNormalizer = new NameNormalizer();
        $products = $cart->getProducts();
        $items = array();

        foreach ($products as $product) {
            $unitPriceTaxExcl = $priceRounder->roundPrice($product['price'], $idCurrency);
            $totalPriceTaxExcl = $priceRounder->roundPrice($product['total'], $idCurrency);
            $totalPriceTaxIncl = $priceRounder->roundPrice($product['total_wt'], $idCurrency);
            $totalTax = $priceRounder->roundPrice($product['total_wt'] - $product['total'], $idCurrency);

            $attributes = isset($product['attributes']) ? $product['attributes'] : '';

            $productName = sprintf('%s %s', $product['name'], $attributes);
            $productName = $nameNormalizer->normalize($productName);

            $item = new PaymentItem();
            $item->setReference($product['reference'] ?: $product['id_product']);
            $item->setName($productName);
            $item->setQuantity($product['cart_quantity']);
            $item->setUnitPrice($unitPriceTaxExcl);
            $item->setTaxRate($product['rate']);
            $item->setTaxAmount($totalTax);
            $item->setGrossTotalAmount($totalPriceTaxIncl);
            $item->setNetTotalAmount($totalPriceTaxExcl);

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get cart discounts as payment item
     *
     * @param Cart $cart
     *
     * @return PaymentItem|null
     */
    protected function getCartDiscountsItem(Cart $cart)
    {
        $idCurrency = $cart->id_currency;
        $priceRounder = new PriceRoundAdapter();
        $discountTaxIncl = $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);

        if (0.0 != $discountTaxIncl) {
            $discountTaxExcl = $cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS);
            $totalTax = $priceRounder->roundPrice($discountTaxIncl - $discountTaxExcl, $idCurrency);
            $averageTaxRate = $cart->getAverageProductsTaxRate() * 100;

            $item = new PaymentItem();
            $item->setReference('discount');
            $item->setName($this->getModule()->l('Discount', 'AbstractRequest'));
            $item->setQuantity(1);
            $item->setUnitPrice(-$discountTaxExcl);
            $item->setTaxRate($averageTaxRate);
            $item->setTaxAmount(-$totalTax);
            $item->setGrossTotalAmount(-$discountTaxIncl);
            $item->setNetTotalAmount(-$discountTaxExcl);

            return $item;
        }

        return null;
    }

    /**
     * Get cart shipping cost as payment item
     *
     * @param Cart $cart
     *
     * @return PaymentItem|null
     */
    protected function getCartShippingItem(Cart $cart)
    {
        $priceRounder = new PriceRoundAdapter();
        $idCurrency = $cart->id_currency;
        $shippingPriceTaxIncl = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);

        if (0.0 != $shippingPriceTaxIncl) {
            $carrier = new Carrier($cart->id_carrier);
            $carrierTaxRate = $carrier->getTaxesRate(new Address($cart->id_address_delivery));
            $shippingPriceTaxExcl = $cart->getOrderTotal(false, Cart::ONLY_SHIPPING);
            $totalTax = $priceRounder->roundPrice($shippingPriceTaxIncl - $shippingPriceTaxExcl, $idCurrency);

            $item = new PaymentItem();
            $item->setReference('shipping');
            $item->setName($this->getModule()->l('Shipping', 'AbstractRequest'));
            $item->setQuantity(1);
            $item->setUnitPrice($shippingPriceTaxExcl);
            $item->setTaxRate($carrierTaxRate);
            $item->setTaxAmount($totalTax);
            $item->setGrossTotalAmount($shippingPriceTaxIncl);
            $item->setNetTotalAmount($shippingPriceTaxExcl);

            return $item;
        }

        return null;
    }

    /**
     * Get cart wrapping cost as payment item
     *
     * @param Cart $cart
     *
     * @return PaymentItem|null
     */
    protected function getCartWrappingItem(Cart $cart)
    {
        $priceRounder = new PriceRoundAdapter();
        $idCurrency = $cart->id_currency;
        $wrappingTaxIncl = $cart->getOrderTotal(true, Cart::ONLY_WRAPPING);

        if (0.0 != $wrappingTaxIncl) {
            $wrappingTaxExcl = $cart->getOrderTotal(false, Cart::ONLY_WRAPPING);
            $totalTax = $priceRounder->roundPrice($wrappingTaxIncl - $wrappingTaxExcl, $idCurrency);

            $item = new PaymentItem();
            $item->setReference('wrapping');
            $item->setName($this->getModule()->l('Wrapping', 'AbstractRequest'));
            $item->setQuantity(1);
            $item->setUnitPrice($wrappingTaxExcl);
            $item->setTaxRate(0);
            $item->setTaxAmount($totalTax);
            $item->setGrossTotalAmount($wrappingTaxIncl);
            $item->setNetTotalAmount($wrappingTaxExcl);

            return $item;
        }

        return null;
    }

    /**
     * Cart discounts, shipping & etc
     *
     * @param Cart $cart
     *
     * @return array|PaymentItem[]
     */
    protected function getCartAdditionalItems(Cart $cart)
    {
        $items = array();

        $discountItem = $this->getCartDiscountsItem($cart);
        if ($discountItem) {
            $items[] = $discountItem;
        }

        $shippingItem = $this->getCartShippingItem($cart);
        if ($shippingItem) {
            $items[] = $shippingItem;
        }

        $wrappingItem = $this->getCartWrappingItem($cart);
        if ($wrappingItem) {
            $items[] = $wrappingItem;
        }

        return $items;
    }

    /**
     * Get order product items for payment
     *
     * @param Order $order
     *
     * @return array|PaymentItem[]
     */
    protected function getOrderProductItems(Order $order)
    {
        $priceRounder = new PriceRoundAdapter();
        $nameNormalizer = new NameNormalizer();
        $idCurrency = $order->id_currency;

        $orderDetails = new \Collection('OrderDetail');
        $orderDetails->where('id_order', '=', $order->id);
        $orderDetails = $orderDetails->getResults();

        $items = array();

        /** @var \OrderDetail $orderDetail */
        foreach ($orderDetails as $orderDetail) {
            $unitPriceTaxExcl = $priceRounder->roundPrice($orderDetail->unit_price_tax_excl, $idCurrency);
            $totalPriceTaxIncl = $priceRounder->roundPrice($orderDetail->total_price_tax_incl, $idCurrency);
            $totalPriceTaxExcl = $priceRounder->roundPrice($orderDetail->total_price_tax_excl, $idCurrency);
            $totalTax = $priceRounder->roundPrice($totalPriceTaxIncl - $totalPriceTaxExcl, $idCurrency);

            $productName = $nameNormalizer->normalize($orderDetail->product_name);

            $item = new PaymentItem();
            $item->setReference($orderDetail->product_reference ?: sprintf('id_product-%d', $orderDetail->product_id));
            $item->setName($productName);
            $item->setQuantity($orderDetail->product_quantity);
            $item->setUnitPrice($unitPriceTaxExcl);
            $item->setTaxRate($orderDetail->tax_rate);
            $item->setTaxAmount($totalTax);
            $item->setGrossTotalAmount($totalPriceTaxIncl);
            $item->setNetTotalAmount($totalPriceTaxExcl);

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get order discounts as payment item
     *
     * @param Order $order
     *
     * @return PaymentItem|null
     */
    protected function getOrderDiscountsItem(Order $order)
    {
        $priceRounder = new PriceRoundAdapter();
        $idCurrency = $order->id_currency;

        if (0.0 != (float) $order->total_discounts) {
            $totalDiscountTaxExcl = $priceRounder->roundPrice($order->total_discounts_tax_excl, $idCurrency);
            $totalDiscountTaxIncl = $priceRounder->roundPrice($order->total_discounts_tax_incl, $idCurrency);
            $totalTax = $priceRounder->roundPrice($totalDiscountTaxIncl - $totalDiscountTaxExcl, $idCurrency);

            $item = new PaymentItem();
            $item->setReference('discount');
            $item->setName($this->getModule()->l('Discount', 'AbstractRequest'));
            $item->setQuantity(1);
            $item->setUnitPrice(-$totalDiscountTaxExcl);
            $item->setTaxRate(0);
            $item->setTaxAmount(-$totalTax);
            $item->setGrossTotalAmount(-$totalDiscountTaxIncl);
            $item->setNetTotalAmount(-$totalDiscountTaxExcl);

            return $item;
        }

        return null;
    }

    /**
     * Get order shipping cost as payment item
     *
     * @param Order $order
     *
     * @return PaymentItem|null
     */
    protected function getOrderShippingItem(Order $order)
    {
        $priceRounder = new PriceRoundAdapter();
        $idCurrency = $order->id_currency;

        if (0.0 != (float) $order->total_shipping) {
            $totalShippingTaxExcl = $priceRounder->roundPrice($order->total_shipping_tax_excl, $idCurrency);
            $totalShippingTaxIncl = $priceRounder->roundPrice($order->total_shipping_tax_incl, $idCurrency);
            $taxAmount = $priceRounder->roundPrice($totalShippingTaxIncl - $totalShippingTaxExcl, $idCurrency);

            $item = new PaymentItem();
            $item->setReference('shipping');
            $item->setName($this->getModule()->l('Shipping', 'AbstractRequest'));
            $item->setQuantity(1);
            $item->setUnitPrice($totalShippingTaxExcl);
            $item->setTaxRate($order->carrier_tax_rate);
            $item->setTaxAmount($taxAmount);
            $item->setGrossTotalAmount($totalShippingTaxIncl);
            $item->setNetTotalAmount($totalShippingTaxExcl);

            return $item;
        }

        return null;
    }

    /**
     * Get order wrapping cost as payment item
     *
     * @param Order $order
     *
     * @return PaymentItem|null
     */
    protected function getOrderWrappingItem(Order $order)
    {
        $priceRounder = new PriceRoundAdapter();
        $idCurrency = $order->id_currency;

        if (0.0 != (float) $order->total_wrapping) {
            $totalTaxExcl = $priceRounder->roundPrice($order->total_wrapping_tax_excl, $idCurrency);
            $totalTaxIncl = $priceRounder->roundPrice($order->total_wrapping_tax_incl, $idCurrency);
            $taxAmount = $priceRounder->roundPrice($totalTaxIncl - $totalTaxExcl, $idCurrency);

            $item = new PaymentItem();
            $item->setReference('wrapping');
            $item->setName($this->getModule()->l('Wrapping', 'AbstractRequest'));
            $item->setQuantity(1);
            $item->setUnitPrice($totalTaxExcl);
            $item->setTaxRate(0);
            $item->setTaxAmount($taxAmount);
            $item->setGrossTotalAmount($totalTaxIncl);
            $item->setNetTotalAmount($totalTaxExcl);

            return $item;
        }

        return null;
    }

    /**
     * Order discounts, shipping & etc
     *
     * @param Order $order
     *
     * @return array|PaymentItem[]
     */
    protected function getOrderAdditionalItems(Order $order)
    {
        $items = array();

        $discountItem = $this->getOrderDiscountsItem($order);
        if ($discountItem) {
            $items[] = $discountItem;
        }

        $shippingItem = $this->getOrderShippingItem($order);
        if ($shippingItem) {
            $items[] = $shippingItem;
        }

        $wrappingItem = $this->getOrderWrappingItem($order);
        if ($wrappingItem) {
            $items[] = $wrappingItem;
        }

        return $items;
    }
}
