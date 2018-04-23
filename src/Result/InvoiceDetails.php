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

class InvoiceDetails
{
    /**
     * @var string
     */
    private $invoiceNumber;

    /**
     * @var string
     */
    private $ocr;

    /**
     * @var string
     */
    private $pdfLink;

    /**
     * @var string
     */
    private $dueDate;

    /**
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * @param string $invoiceNumber
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * @return string
     */
    public function getOcr()
    {
        return $this->ocr;
    }

    /**
     * @param string $ocr
     */
    public function setOcr($ocr)
    {
        $this->ocr = $ocr;
    }

    /**
     * @return string
     */
    public function getPdfLink()
    {
        return $this->pdfLink;
    }

    /**
     * @param string $pdfLink
     */
    public function setPdfLink($pdfLink)
    {
        $this->pdfLink = $pdfLink;
    }

    /**
     * @return string
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param string $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }
}
