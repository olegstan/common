<?php

namespace Common\Models\Traits\Catalog;

trait SearchActiveCatalogTrait
{
    /**
     * @return array
     */
    public function getItemData(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getSymbolName(),
            'type_id' => $this->getType(),
            'type_text' => $this->getTypeText(),
            'currency_id' => $this->getCurrency(),
            'ticker' => $this->getCatalog(),
            'facevalue' => $this->getFaceValue(),
            'couponfrequency' => $this->getCouponFrequency(),
            'coupondate' => $this->getCouponDate(),
            'couponpercent' => $this->getCouponPercent(),
            'couponvalue' => $this->getCouponValue(),
            'decimals' => $this->getDecimals(),
            'lotsize' => $this->getLotSize(),
            'symbol' => $this->getSymbol(),
            'country' => $this->getCountry(),
            'industry' => $this->getIndustry(),
            'sector' => $this->getSector(),
            'capitalization' => $this->getCapitalization(),
        ];
    }
}