<?php

namespace Invertus\Dibs\Result;

class CardDetails
{
    /**
     * @var string
     */
    private $maskedPan;

    /**
     * @var string
     */
    private $expirityDate;

    /**
     * @return string
     */
    public function getMaskedPan()
    {
        return (string) $this->maskedPan;
    }

    /**
     * @param string $maskedPan
     */
    public function setMaskedPan($maskedPan)
    {
        $this->maskedPan = $maskedPan;
    }

    /**
     * @return string
     */
    public function getExpirityDate()
    {
        return $this->expirityDate;
    }

    /**
     * @param string $expirityDate
     */
    public function setExpirityDate($expirityDate)
    {
        $this->expirityDate = $expirityDate;
    }
}
