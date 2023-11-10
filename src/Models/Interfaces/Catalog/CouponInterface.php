<?php

namespace Common\Models\Interfaces\Catalog;

use Carbon\Carbon;
use Common\Models\Currency;

interface CouponInterface
{
    public function getValue(): ?float;

    public function getCouponDate(): ?Carbon;

    public function getCouponValue(Currency $currency): ?float;
}