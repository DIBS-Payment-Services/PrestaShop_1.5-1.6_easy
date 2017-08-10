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
use Invertus\Dibs\Payment\PaymentItem;
use Order;

/**
 * Class AbstractAction
 *
 * @package Invertus\Dibs\Action
 */
abstract class AbstractAction
{
    /**
     * Module instance used for translations
     *
     * @return \Dibs
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
        $products = $cart->getProducts();
        $items = array();

        foreach ($products as $product) {
            $taxAmountTotal = $product['total_wt'] - $product['total'];

            $attributes = isset($product['attributes']) ? $product['attributes'] : '';

            $item = new PaymentItem();
            $item->setReference($product['reference'] ?: $product['id_product']);
            $item->setName(sprintf('%s, %s', $product['name'], $attributes));
            $item->setQuantity($product['cart_quantity']);
            $item->setUnitPrice($product['price']);
            $item->setTaxRate($product['rate']);
            $item->setTaxAmount($taxAmountTotal);
            $item->setGrossTotalAmount($product['total_wt']);
            $item->setNetTotalAmount($product['total']);

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
        $discountTaxIncl = $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);

        if (0.0 != $discountTaxIncl) {
            $discountTaxExcl = $cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS);

            $item = new PaymentItem();
            $item->setReference('discount');
            $item->setName($this->getModule()->l('Discount', 'AbstractRequest'));
            $item->setQuantity(1);
            $item->setUnitPrice(-$discountTaxExcl);
            $item->setTaxRate(0);
            $item->setTaxAmount(0);
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
        $shippingPriceTaxIncl = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);

        if (0.0 != $shippingPriceTaxIncl) {
            $shippingPriceTaxExcl = $cart->getOrderTotal(false, Cart::ONLY_SHIPPING);

            $item = new PaymentItem();
            $item->setReference('shipping');
            $item->setName($this->getModule()->l('Shipping', 'AbstractRequest'));
            $item->setQuantity(1);
            $item->setUnitPrice($shippingPriceTaxExcl);
            $item->setTaxRate(0);
            $item->setTaxAmount($shippingPriceTaxIncl - $shippingPriceTaxIncl);
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
        $wrappingTaxIncl = $cart->getOrderTotal(true, Cart::ONLY_WRAPPING);

        if (0.0 != $wrappingTaxIncl) {
            $wrappingTaxExcl = $cart->getOrderTotal(false, Cart::ONLY_WRAPPING);

            $item = new PaymentItem();
            $item->setReference('wrapping');
            $item->setName($this->getModule()->l('Wrapping', 'AbstractRequest'));
            $item->setQuantity(1);
            $item->setUnitPrice($wrappingTaxExcl);
            $item->setTaxRate(0);
            $item->setTaxAmount($wrappingTaxIncl - $wrappingTaxExcl);
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
        $orderDetails = new \Collection('OrderDetail');
        $orderDetails->where('id_order', '=', $order->id);
        $orderDetails = $orderDetails->getResults();

        $items = array();

        /** @var \OrderDetail $orderDetail */
        foreach ($orderDetails as $orderDetail) {
            $totalTax = $orderDetail->total_price_tax_incl - $orderDetail->total_price_tax_excl;

            $item = new PaymentItem();
            $item->setReference($orderDetail->product_reference ?: sprintf('id_product-%d', $orderDetail->product_id));
            $item->setName($orderDetail->product_name);
            $item->setQuantity($orderDetail->product_quantity);
            $item->setUnitPrice($orderDetail->unit_price_tax_excl);
            $item->setTaxRate($orderDetail->tax_rate);
            $item->setTaxAmount($totalTax);
            $item->setGrossTotalAmount($orderDetail->total_price_tax_incl);
            $item->setNetTotalAmount($orderDetail->total_price_tax_excl);

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
        if (0.0 != (float) $order->total_discounts) {
            $item = new PaymentItem();
            $item->setReference('discount');
            $item->setName($this->getModule()->l('Discount', 'AbstractRequest'));
            $item->setQuantity(1);
            $item->setUnitPrice(-$order->total_discounts_tax_excl);
            $item->setTaxRate(0);
            $item->setTaxAmount(0);
            $item->setGrossTotalAmount(-$order->total_discounts_tax_incl);
            $item->setNetTotalAmount(-$order->total_discounts_tax_excl);

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
        if (0.0 != (float) $order->total_shipping) {
            $item = new PaymentItem();
            $item->setReference('shipping');
            $item->setName($this->getModule()->l('Shipping', 'AbstractRequest'));
            $item->setQuantity(1);
            $item->setUnitPrice($order->total_shipping_tax_excl);
            $item->setTaxRate($order->carrier_tax_rate);
            $item->setTaxAmount($order->total_shipping_tax_incl - $order->total_shipping_tax_excl);
            $item->setGrossTotalAmount($order->total_shipping_tax_incl);
            $item->setNetTotalAmount($order->total_shipping_tax_excl);

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
        if (0.0 != (float) $order->total_wrapping) {
            $item = new PaymentItem();
            $item->setReference('wrapping');
            $item->setName($this->getModule()->l('Wrapping', 'AbstractRequest'));
            $item->setQuantity(1);
            $item->setUnitPrice($order->total_wrapping_tax_excl);
            $item->setTaxRate(0);
            $item->setTaxAmount($order->total_wrapping_tax_incl - $order->total_wrapping_tax_excl);
            $item->setGrossTotalAmount($order->total_wrapping_tax_incl);
            $item->setNetTotalAmount($order->total_wrapping_tax_excl);

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
