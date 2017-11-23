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

namespace Invertus\DibsEasy\Result;

class PaymentDetail
{
    /**
     * @var string
     */
    private $paymentType;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var InvoiceDetails
     */
    private $invoiceDetails;

    /**
     * @var CardDetails
     */
    private $cardDetails;

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return (string) $this->paymentType;
    }

    /**
     * @param string $paymentType
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    /**
     * @return InvoiceDetails
     */
    public function getInvoiceDetails()
    {
        return $this->invoiceDetails;
    }

    /**
     * @param InvoiceDetails $invoiceDetails
     */
    public function setInvoiceDetails(InvoiceDetails $invoiceDetails)
    {
        $this->invoiceDetails = $invoiceDetails;
    }

    /**
     * @return CardDetails
     */
    public function getCardDetails()
    {
        return $this->cardDetails;
    }

    /**
     * @param CardDetails $cardDetails
     */
    public function setCardDetails(CardDetails $cardDetails)
    {
        $this->cardDetails = $cardDetails;
    }
}
