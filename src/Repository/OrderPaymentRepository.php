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

namespace Invertus\DibsEasy\Repository;

use Db;
use DbQuery;

/**
 * Class OrderPaymentRepository
 *
 * @package Invertus\DibsEasy\Repository
 */
class OrderPaymentRepository
{
    /**
     * @var Db
     */
    private $db;

    /**
     * OrderPaymentRepository constructor.
     *
     * @param Db $db
     */
    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * Find DIBS payment ID by PrestaShop order ID
     *
     * @param int $idOrder
     *
     * @return false|null|string
     */
    public function findPaymentIdByOrderId($idOrder)
    {
        $query = new DbQuery();
        $query->select('dp.id_payment');
        $query->from('dibs_payment', 'dp');
        $query->where('dp.id_order = '.(int)$idOrder);

        $result = $this->db->getValue($query);

        return $result;
    }

    /**
     * Find DIBS charge ID by PrestaShop order ID
     *
     * @param int $idOrder
     *
     * @return false|null|string
     */
    public function findChargeIdByOrderId($idOrder)
    {
        $query = new DbQuery();
        $query->select('dp.id_charge');
        $query->from('dibs_payment', 'dp');
        $query->where('dp.id_order = '.(int)$idOrder);

        $result = $this->db->getValue($query);

        return $result;
    }

    /**
     * @param int $idOrder
     *
     * @return \DibsOrderPayment|false
     */
    public function findOrderPaymentByOrderId($idOrder)
    {
        $collection = new \Collection('DibsOrderPayment');
        $collection->where('id_order', '=', $idOrder);
        $orderPayment = $collection->getFirst();

        return $orderPayment;
    }

    /**
     * @param int $idCart
     *
     * @return \DibsOrderPayment|false
     */
    public function findOrderPaymentByCartId($idCart)
    {
        $collection = new \Collection('DibsOrderPayment');
        $collection->where('id_cart', '=', $idCart);
        $orderPayment = $collection->getFirst();

        return $orderPayment;
    }
}
