<?php

namespace Invertus\Dibs\Result;

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
