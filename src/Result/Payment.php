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

namespace Invertus\Dibs\Result;

class Payment
{
    /**
     * @var string
     */
    private $paymentId;

    /**
     * @var Summary
     */
    private $summary;

    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @var PaymentDetail
     */
    private $paymentDetail;

    /**
     * @var OrderDetail
     */
    private $orderDetail;

    /**
     * @return string
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @param string $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * @return Summary
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param Summary $summary
     */
    public function setSummary(Summary $summary)
    {
        $this->summary = $summary;
    }

    /**
     * @return Consumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }

    /**
     * @param Consumer $consumer
     */
    public function setConsumer(Consumer $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * @return PaymentDetail
     */
    public function getPaymentDetail()
    {
        return $this->paymentDetail;
    }

    /**
     * @param PaymentDetail $paymentDetail
     */
    public function setPaymentDetail(PaymentDetail $paymentDetail)
    {
        $this->paymentDetail = $paymentDetail;
    }

    /**
     * @return OrderDetail
     */
    public function getOrderDetail()
    {
        return $this->orderDetail;
    }

    /**
     * @param OrderDetail $orderDetail
     */
    public function setOrderDetail(OrderDetail $orderDetail)
    {
        $this->orderDetail = $orderDetail;
    }
}
