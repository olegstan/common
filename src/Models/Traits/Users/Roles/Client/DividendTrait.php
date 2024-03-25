<?php
namespace Common\Models\Traits\Users\Roles\Client;

use App\Models\Actives\Active;
use App\Models\Actives\ActiveCoupon;
use App\Models\Actives\ActiveDividend;
use Carbon\Carbon;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeCoupon;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeDividend;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Common\Models\Users\Roles\Types\Client;
use LaravelRest\Http\Transformers\BaseTransformer;

/**
 * Trait StrategyTrait
 *
 * @mixin Client
 *
 * @package App\Models\Traits
 */
trait DividendTrait
{
    /**
     * @param $currency
     * @param $iterateDate
     * @param $endDate
     * @return array
     */
    public function getChartDividend($currency, $iterateDate, $endDate)
    {
        $now = Carbon::now();

        $actives = $this
            ->actives()
            ->with('trades_ordered', 'trades_ordered.currency')
            ->with('buy_trades', 'buy_trades.currency')
            ->with('sell_trades', 'sell_trades.currency')
            ->with('dividends', 'dividends.currency')
            ->with('valuations', 'valuations.currency')
            ->with('payments', 'payments.currency')
            ->with('sell_currency')
            ->with('buy_currency')
            ->with('sell_account')
            ->with('buy_account')
            ->with('coupons')
            ->with('last_coupon')
            ->with('item', 'item.coupons', 'item.dividends')
            ->whereIntegerInRaw('type_id', [DefinitionActiveConst::STOCK, DefinitionActiveConst::BOND])
            ->get();

        $data = [];

//        $iterateDate = $this->birth_at->copy()->subYear()->startOfMonth()->startOfDay();
//        $endDate = $this->birth_at->copy()->addYears(90)->startOfMonth()->startOfDay();


//        $this->addPoint(Carbon::now(), '#ff0000', 'Сегодня');
//        $this->addPoint($this->birth_at->copy()->addYears($this->retired_age), '#7B7B7B', 'Карьера');

        $i = 0;
        while ($iterateDate->lessThan($endDate))
        {
            $points = [];
            $date = $iterateDate->format('Y-m-d');

            $data[$i] = [
                'index' => $i,
                'category' => $date,
                'value' => 0,
                'dividend' => 0,
                'coupons' => 0,
                'plan_coupons' => 0,
                'points' => []
            ];

//            if ($i != 0)
//            {
//                $prev = $i - 1;
////                $data[$i]['dividend'] += $data[$prev]['dividend'];
////                $data[$i]['value'] += $data[$prev]['value'];
//            }

            /**
             * @var Active $active
             */
            foreach ($actives as &$active)
            {
                foreach ($active->dividends as $dividend)
                {
                    /**
                     * @var ActiveDividend $dividend
                     */
                    if ($dividend->getDividendDate()->startOfMonth()->format('Y-m-d') === $date)
                    {
                        foreach($active->buy_trades as $trade)
                        {
                            if ($trade && $trade->trade_at && $dividend->getDividendDate() && $dividend->getDividendDate()->greaterThan($trade->trade_at))
                            {
                                $sum = $trade->count * $dividend->getDividendValue();
                                $data[$i]['dividend'] += $sum;
                                $active->dividends_sum += $sum;
                            }
                        }
                    }
                }

                if(isset($active->item->dividends))
                {
                    foreach ($active->item->dividends as $dividend)
                    {
                        /**
                         * @var MoscowExchangeDividend $dividend
                         */
                        if($dividend->getDividendDate()->startOfMonth()->format('Y-m-d') === $date)
                        {
                            foreach($active->buy_trades as $trade)
                            {
                                if ($trade && $trade->trade_at && $dividend->getDividendDate() && $dividend->getDividendDate()->greaterThan($trade->trade_at))
                                {
                                    $sum = $trade->count * $active->item->getLotSize() * $dividend->getDividendValue();
                                    $data[$i]['dividend'] += $sum;
                                    $active->dividends_sum += $sum;
                                }
                            }
                        }
                    }
                }

                foreach($active->coupons as $coupon)
                {
                    /**
                     * @var ActiveCoupon $coupon
                     */
                    if($coupon->getCouponDate()->startOfMonth()->format('Y-m-d') === $date)
                    {
                        if($iterateDate->lessThanOrEqualTo($now))
                        {
                            foreach($active->buy_trades as $trade)
                            {
                                if ($trade && $trade->trade_at && $coupon->getCouponValue(Currency::getByCode(Currency::RUB)) && $coupon->getCouponDate()->greaterThan($trade->trade_at))
                                {
                                    $sum = $trade->count * $coupon->getCouponValue(Currency::getByCode(Currency::RUB));
                                    $data[$i]['coupons'] += $sum;
                                    $data[$i]['plan_coupons'] += 0;
                                    $active->coupons_sum += $sum;
                                }
                            }
                        }else{
                            foreach($active->buy_trades as $trade)
                            {
                                if ($trade && $trade->trade_at && $coupon->getCouponValue(Currency::getByCode(Currency::RUB)) && $coupon->getCouponDate()->greaterThan($trade->trade_at))
                                {
                                    $data[$i]['plan_coupons'] += $trade->count * $coupon->getCouponValue(Currency::getByCode(Currency::RUB));
                                    $data[$i]['coupons'] += 0;
                                }
                            }
                        }
                    }
                }

                if(isset($active->item->coupons))
                {
                    foreach ($active->item->coupons as $coupon)
                    {
                        /**
                         * @var MoscowExchangeCoupon $coupon
                         */
                        if($coupon->getCouponDate()->startOfMonth()->format('Y-m-d') === $date)
                        {
                            if($iterateDate->lessThanOrEqualTo($now))
                            {
                                foreach($active->buy_trades as $trade)
                                {
                                    if ($trade && $trade->trade_at && $coupon->getCouponValue(Currency::getByCode(Currency::RUB)) && $coupon->getCouponDate()->greaterThan($trade->trade_at))
                                    {
                                        $sum = $trade->count * $coupon->getCouponValue(Currency::getByCode(Currency::RUB));
                                        $data[$i]['coupons'] += $sum;
                                        $data[$i]['plan_coupons'] += 0;
                                        $active->coupons_sum += $sum;
                                    }
                                }
                            }else{
                                foreach($active->buy_trades as $trade)
                                {
                                    if ($trade && $trade->trade_at && $coupon->getCouponValue(Currency::getByCode(Currency::RUB)) && $coupon->getCouponDate()->greaterThan($trade->trade_at))
                                    {
                                        $data[$i]['plan_coupons'] += $trade->count * $coupon->getCouponValue(Currency::getByCode(Currency::RUB));
                                        $data[$i]['coupons'] += 0;
                                    }
                                }
                            }
                        }
                    }
                }

                $data[$i]['value'] = 0;
//                $data[$i]['value'] += $active->getInvest($iterateDate->copy(), $currency, $points);
            }

            $data[$i]['points'] = $points;

            $iterateDate->addMonth()->startOfDay();
            $i++;
        }

        return [$data, $actives->map(function ($row){

            $data = BaseTransformer::createTransformer($row)->transform($row);

            $data['coupons_sum'] = $row->coupons_sum;
            $data['dividends_sum'] = $row->dividends_sum;

            return $data;
        })];
    }
}