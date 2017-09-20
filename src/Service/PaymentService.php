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

namespace Invertus\Dibs\Service;

use Cart;
use Currency;
use Invertus\Dibs\Adapter\LinkAdapter;
use Invertus\Dibs\Adapter\ToolsAdapter;
use Invertus\Dibs\Payment\PaymentCancelRequest;
use Invertus\Dibs\Payment\PaymentChargeRequest;
use Invertus\Dibs\Payment\PaymentCreateRequest;
use Invertus\Dibs\Payment\PaymentGetRequest;
use Invertus\Dibs\Payment\PaymentRefundRequest;
use Invertus\Dibs\Repository\OrderPaymentRepository;
use Invertus\Dibs\Result\Address;
use Invertus\Dibs\Result\CardDetails;
use Invertus\Dibs\Result\Consumer;
use Invertus\Dibs\Result\InvoiceDetails;
use Invertus\Dibs\Result\OrderDetail;
use Invertus\Dibs\Result\Payment;
use Invertus\Dibs\Result\PaymentDetail;
use Invertus\Dibs\Result\Person;
use Invertus\Dibs\Result\PhoneNumber;
use Invertus\Dibs\Result\Summary;

/**
 * Class PaymentService
 *
 * @package Invertus\Dibs\Service
 */
class PaymentService
{
    /**
     * @var ApiRequest
     */
    private $apiRequest;

    /**
     * @var ToolsAdapter
     */
    private $toolsAdapter;

    /**
     * @var LinkAdapter
     */
    private $linkAdapter;

    /**
     * @var OrderPaymentRepository
     */
    private $orderPaymentRepository;

    /**
     * PaymentService constructor.
     *
     * @param ApiRequest $apiRequest
     * @param OrderPaymentRepository $orderPaymentRepository
     * @param ToolsAdapter $toolsAdapter
     * @param LinkAdapter $linkAdapter
     */
    public function __construct(
        ApiRequest $apiRequest,
        OrderPaymentRepository $orderPaymentRepository,
        ToolsAdapter $toolsAdapter,
        LinkAdapter $linkAdapter
    ) {
        $this->apiRequest = $apiRequest;
        $this->toolsAdapter = $toolsAdapter;
        $this->linkAdapter = $linkAdapter;
        $this->orderPaymentRepository = $orderPaymentRepository;
    }

    /**
     * Create payment in DIBS system
     *
     * @param PaymentCreateRequest $paymentCreateRequest
     *
     * @return string Payment ID in DIBS
     */
    public function createPayment(PaymentCreateRequest $paymentCreateRequest)
    {
        $params = $paymentCreateRequest->toArray();

        $body = $this->toolsAdapter->jsonEncode($params);
        $response = $this->apiRequest->post('/v1/payments', $body);

        if ($response->isSuccess()) {
            $paymentId = $response->getFromBody('paymentId');

            return $paymentId;
        }

        return null;
    }

    /**
     * Cancel created payment in DIBS
     *
     * @param PaymentCancelRequest $paymentCancelRequest
     *
     * @return bool
     */
    public function cancelPayment(PaymentCancelRequest $paymentCancelRequest)
    {
        $params = $paymentCancelRequest->toArray();

        $body = $this->toolsAdapter->jsonEncode($params);
        $endpoint = sprintf('/v1/payments/%s/cancels', $paymentCancelRequest->getPaymentId());

        $response = $this->apiRequest->post($endpoint, $body);

        return $response->isSuccess();
    }

    /**
     * Charge payment in DIBS
     *
     * @param PaymentChargeRequest $paymentChargeRequest
     *
     * @return string|false If ok, then charge ID will be returned, or FALSE otherwise
     */
    public function chargePayment(PaymentChargeRequest $paymentChargeRequest)
    {
        $params = $paymentChargeRequest->toArray();

        $body = $this->toolsAdapter->jsonEncode($params);
        $endpoint = sprintf('/v1/payments/%s/charges', $paymentChargeRequest->getPaymentId());

        $response = $this->apiRequest->post($endpoint, $body);
        if (!$response->isSuccess()) {
            return false;
        }

        return $response->getFromBody('chargeId');
    }

    /**
     * Make full or partial payment refunds
     *
     * @param PaymentRefundRequest $paymentRefundRequest
     *
     * @return bool
     */
    public function refundPayment(PaymentRefundRequest $paymentRefundRequest)
    {
        $params = $paymentRefundRequest->toArray();

        $body = $this->toolsAdapter->jsonEncode($params);
        $endpoint = sprintf('/v1/charges/%s/refunds', $paymentRefundRequest->getChargeId());

        $response = $this->apiRequest->post($endpoint, $body);

        return $response->isSuccess();
    }

    /**
     * @param PaymentGetRequest $paymentGetRequest
     *
     * @return Payment|null
     */
    public function getPayment(PaymentGetRequest $paymentGetRequest)
    {
        $response = $this->apiRequest->get(sprintf('/v1/payments/%s', $paymentGetRequest->getPaymentId()));

        if (!$response->isSuccess()) {
            return null;
        }

        // All the mappings are performed below
        // @todo: mappings should be performed/moved somewhere else

        $paymentArray = $response->getFromBody('payment');

        $summry = new Summary();
        $summry->setReservedAmount(
            isset($paymentArray['summary']['reservedAmount']) ?
                $paymentArray['summary']['reservedAmount'] :
                0
        );

        $billingAddress = new Address();
        $billingAddress->setAddressLine1(
            isset($paymentArray['consumer']['billingAddress']['addressLine1']) ?
                $paymentArray['consumer']['billingAddress']['addressLine1'] :
                null
        );
        $billingAddress->setAddressLine2(
            isset($paymentArray['consumer']['billingAddress']['addressLine2']) ?
                $paymentArray['consumer']['billingAddress']['addressLine2'] :
                null
        );
        $billingAddress->setPostalCode(
            isset($paymentArray['consumer']['billingAddress']['postalCode']) ?
                $paymentArray['consumer']['billingAddress']['postalCode'] :
                null
        );
        $billingAddress->setCity(
            isset($paymentArray['consumer']['billingAddress']['city']) ?
                $paymentArray['consumer']['billingAddress']['city'] :
                null
        );
        $billingAddress->setCountry(
            isset($paymentArray['consumer']['billingAddress']['country']) ?
                $paymentArray['consumer']['billingAddress']['country'] :
                null
        );

        $shippingAddress = new Address();
        $shippingAddress->setAddressLine1(
            isset($paymentArray['consumer']['shippingAddress']['addressLine1']) ?
                $paymentArray['consumer']['shippingAddress']['addressLine1'] :
                null
        );
        $shippingAddress->setAddressLine2(
            isset($paymentArray['consumer']['shippingAddress']['addressLine2']) ?
                $paymentArray['consumer']['shippingAddress']['addressLine2'] :
                null
        );
        $shippingAddress->setPostalCode(
            isset($paymentArray['consumer']['shippingAddress']['postalCode']) ?
                $paymentArray['consumer']['shippingAddress']['postalCode'] :
                null
        );
        $shippingAddress->setCity(
            isset($paymentArray['consumer']['shippingAddress']['city']) ?
                $paymentArray['consumer']['shippingAddress']['city'] :
                null
        );
        $shippingAddress->setCountry(
            isset($paymentArray['consumer']['shippingAddress']['country']) ?
                $paymentArray['consumer']['shippingAddress']['country'] :
                null
        );

        $person = new Person();
        $person->setDateOfBirth(
            isset($paymentArray['consumer']['privatePerson']['dateOfBirth']) ?
                $paymentArray['consumer']['privatePerson']['dateOfBirth'] :
                null
        );
        $person->setFirstName(
            isset($paymentArray['consumer']['privatePerson']['firstName']) ?
                $paymentArray['consumer']['privatePerson']['firstName'] :
                null
        );
        $person->setLastName(
            isset($paymentArray['consumer']['privatePerson']['lastName']) ?
                $paymentArray['consumer']['privatePerson']['lastName'] :
                null
        );
        $person->setEmail(
            isset($paymentArray['consumer']['privatePerson']['email']) ?
                $paymentArray['consumer']['privatePerson']['email'] :
                null
        );
        $person->setMerchantReference(
            isset($paymentArray['consumer']['privatePerson']['merchantReference']) ?
                $paymentArray['consumer']['privatePerson']['merchantReference'] :
                null
        );

        $personPhoneNumber = new PhoneNumber();
        $personPhoneNumber->setPrefix(
            isset($paymentArray['consumer']['privatePerson']['phoneNumber']['prefix']) ?
                $paymentArray['consumer']['privatePerson']['phoneNumber']['prefix'] :
                null
        );
        $personPhoneNumber->setNumber(
            isset($paymentArray['consumer']['privatePerson']['phoneNumber']['number']) ?
                $paymentArray['consumer']['privatePerson']['phoneNumber']['number'] :
                null
        );
        $person->setPhoneNumber($personPhoneNumber);

        $orderDetail = new OrderDetail();
        $orderDetail->setAmount(
            isset($paymentArray['orderDetails']['amount']) ?
                $paymentArray['orderDetails']['amount'] :
                null
        );
        $orderDetail->setCurrency(
            isset($paymentArray['orderDetails']['currency']) ?
                $paymentArray['orderDetails']['currency'] :
                null
        );
        $orderDetail->setReference(
            isset($paymentArray['orderDetails']['reference']) ?
                $paymentArray['orderDetails']['reference'] :
                null
        );

        $invoiceDetails = new InvoiceDetails();
        $invoiceDetails->setInvoiceNumber(
            isset($paymentArray['paymentDetails']['invoiceDetails']['invoiceNumber']) ?
            $paymentArray['paymentDetails']['invoiceDetails']['invoiceNumber'] :
            null
        );
        $invoiceDetails->setInvoiceNumber(
            isset($paymentArray['paymentDetails']['invoiceDetails']['ocr']) ?
            $paymentArray['paymentDetails']['invoiceDetails']['ocr'] :
            null
        );
        $invoiceDetails->setInvoiceNumber(
            isset($paymentArray['paymentDetails']['invoiceDetails']['pdfLink']) ?
            $paymentArray['paymentDetails']['invoiceDetails']['pdfLink'] :
            null
        );
        $invoiceDetails->setInvoiceNumber(
            isset($paymentArray['paymentDetails']['invoiceDetails']['dueDate']) ?
            $paymentArray['paymentDetails']['invoiceDetails']['dueDate'] :
            null
        );

        $cardDetails = new CardDetails();
        $cardDetails->setMaskedPan(
            isset($paymentArray['paymentDetails']['cardDetails']['maskedPan']) ?
                $paymentArray['paymentDetails']['cardDetails']['maskedPan'] :
                null
        );
        $cardDetails->setMaskedPan(
            isset($paymentArray['paymentDetails']['cardDetails']['expiryDate']) ?
                $paymentArray['paymentDetails']['cardDetails']['expiryDate'] :
                null
        );

        $paymentDetail = new PaymentDetail();
        $paymentDetail->setPaymentType(
            isset($paymentArray['paymentDetails']['paymentType']) ?
                $paymentArray['paymentDetails']['paymentType'] :
                null
        );
        $paymentDetail->setPaymentMethod(
            isset($paymentArray['paymentDetails']['paymentMethod']) ?
                $paymentArray['paymentDetails']['paymentMethod'] :
                null
        );
        $paymentDetail->setInvoiceDetails($invoiceDetails);
        $paymentDetail->setCardDetails($cardDetails);

        $consumer = new Consumer();
        $consumer->setBillingAddress($billingAddress);
        $consumer->setShippingAddress($shippingAddress);
        $consumer->setPrivatePerson($person);

        $payment = new Payment();
        $payment->setPaymentId($paymentArray['paymentId']);
        $payment->setSummary($summry);
        $payment->setConsumer($consumer);
        $payment->setOrderDetail($orderDetail);
        $payment->setPaymentDetail($paymentDetail);

        return $payment;
    }
}
