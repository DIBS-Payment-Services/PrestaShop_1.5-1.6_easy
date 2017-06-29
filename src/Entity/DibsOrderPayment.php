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
 * Class DibsPayment
 */
class DibsOrderPayment extends ObjectModel
{
    /**
     * @var int
     */
    public $id_order;

    /**
     * @var int
     */
    public $id_cart;

    /**
     * @var string This is payment ID in DIBS system
     */
    public $id_payment;

    /**
     * @var string Charge ID in DIBS system
     */
    public $id_charge;

    /**
     * @var bool
     */
    public $is_canceled;

    /**
     * @var bool
     */
    public $is_charged;

    /**
     * @var bool
     */
    public $is_refunded;

    /**
     * @var bool
     */
    public $is_reserved;

    /**
     * @var bool
     */
    public $is_partially_refunded;

    /**
     * @var array
     */
    public static $definition = array(
        'primary' => 'id_dibs_payment',
        'table' => 'dibs_payment',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_cart' => array('type' => self::TYPE_INT, 'required' => 1, 'validate' => 'isUnsignedInt'),
            'id_payment' => array('type' => self::TYPE_STRING, 'required' => 1),
            'id_charge' => array('type' => self::TYPE_STRING,),
            'is_canceled' => array('type' => self::TYPE_BOOL),
            'is_charged' => array('type' => self::TYPE_BOOL),
            'is_refunded' => array('type' => self::TYPE_BOOL),
            'is_reserved' => array('type' => self::TYPE_BOOL),
            'is_partially_refunded' => array('type' => self::TYPE_BOOL),
        ),
    );

    /**
     * Check if payment can be canceled
     *
     * @return bool
     */
    public function canBeCanceled()
    {
        return $this->is_canceled == 0 &&
            $this->is_charged == 0 &&
            $this->is_reserved == 1;
    }

    /**
     * Check if payment can be charged
     *
     * @return bool
     */
    public function canBeCharged()
    {
        return $this->is_canceled == 0 &&
            $this->is_charged == 0 &&
            $this->is_reserved;
    }

    /**
     * Check if payment can be refunded
     *
     * @return bool
     */
    public function canBeRefunded()
    {
        return $this->is_charged == 1 &&
            $this->is_refunded == 0 &&
            $this->is_partially_refunded == 0;
    }

    /**
     * Check if payment can be partially refunded
     *
     * @return bool
     */
    public function canBePartiallyRefunded()
    {
        return $this->is_charged == 1 &&
            $this->is_refunded == 0;
    }
}
