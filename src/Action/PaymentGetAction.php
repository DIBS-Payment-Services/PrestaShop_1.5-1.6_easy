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

use Invertus\Dibs\Payment\PaymentGetRequest;
use Invertus\Dibs\Result\Payment;
use Invertus\Dibs\Service\PaymentService;

class PaymentGetAction
{
    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * PaymentGetAction constructor.
     *
     * @param PaymentService $paymentService
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @param string $paymentId
     *
     * @return Payment|null
     */
    public function getPayment($paymentId)
    {
        static $payment;

        if ($payment) {
            return $payment;
        }

        $request = new PaymentGetRequest();
        $request->setPaymentId($paymentId);

        $payment = $this->paymentService->getPayment($request);

        return $payment;
    }
}
