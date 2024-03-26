<?php

namespace Common\Transformers\Users\Crm;

use Common\Models\Users\Crm\UserDevice;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserDeviceTransformer extends BaseTransformer
{
    /**
     * @param UserDevice $model
     *
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'user_id' => $model->user_id,
            'token' => $model->token,
            'brand' => $model->brand,
            'design_name' => $model->design_name,
            'device_name' => $model->device_name,
            'device_type' => $model->device_type,
            'device_manufacturer' => $model->device_manufacturer,
            'model_id' => $model->model_id,
            'model_name' => $model->model_name,
            'os_build_fingerprint' => $model->os_build_fingerprint,
            'os_build_id' => $model->os_build_id,
            'os_internal_build_id' => $model->os_internal_build_id,
            'os_name' => $model->os_name,
            'os_version' => $model->os_version,
            'platform_api_level' => $model->platform_api_level,
            'product_name' => $model->product_name,
            'supported_cpu_architectures' => $model->supported_cpu_architectures,
            'total_memory' => $model->total_memory,
        ];

        return $this->withRelations($data, $model);
    }

}
