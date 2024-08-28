<?php

namespace Common\Transformers\Catalog\MoscowExchange;

use Carbon\Carbon;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use LaravelRest\Http\Transformers\BaseTransformer;

class MoscowExchangeStockTransformer extends BaseTransformer
{
    /**
     * @param MoscowExchangeStock $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'secid' => $model->getSymbol(),
            'symbol' => $model->getSymbol(),
            'shortname' => $model->shortname,
            'regnumber' => $model->regnumber,
            'name' => $model->name,
            'isin' => $model->isin,
            'is_traded' => $model->is_traded,
            'emitent_id' => $model->emitent_id,
            'emitent_title' => $model->emitent_title,
            'emitent_inn' => $model->emitent_inn,
            'emitent_okpo' => $model->emitent_okpo,
            'gosreg' => $model->gosreg,
            'type' => $model->type,
            'group' => $model->group,
            'primary_boardid' => $model->primary_boardid,
            'marketprice_boardid' => $model->marketprice_boardid,
            'lotsize' => $model->getLotSize(Carbon::now()),
            'icon' => $model->getIcon(),
            'expiration' => $model->expiration,
            'boardid' => $model->boardid,
            'prevsettleprice' => $model->prevsettleprice,
            'minstep' => $model->minstep,
            'lasttradedate' => $model->lasttradedate,
            'sectype' => $model->sectype,
            'assetcode' => $model->assetcode,
            'prevopenposition' => $model->prevopenposition,
            'lotvolume' => $model->lotvolume,
            'initialmargin' => $model->initialmargin,
            'highlimit' => $model->highlimit,
            'lowlimit' => $model->lowlimit,
            'stepprice' => $model->stepprice,
            'lastsettleprice' => $model->lastsettleprice,
            'prevprice' => $model->prevprice,
            'imtime' => $model->imtime,
            'buysellfee' => $model->buysellfee,
            'scalperfee' => $model->scalperfee,
            'negotiatedfee' => $model->negotiatedfee,
            'ticker' => $model->getMorphClass(),
        ];

        return $this->withRelations($data, $model);
    }

}