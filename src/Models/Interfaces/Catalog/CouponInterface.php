<?php

namespace Common\Models\Interfaces\Catalog;

use Common\Models\Currency;
use Carbon\Carbon;

interface CouponInterface
{
    public function getCouponDate(): ?Carbon;

    public function getCouponValue(Currency $currency): ?float;
}