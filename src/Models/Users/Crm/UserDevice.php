<?php

namespace Common\Models\Users\Crm;

use Common\Models\BaseModel;

/**
 * Class UserDevice
 * @package Common\Models\Users\Crm
 */
class UserDevice extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_devices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'token',
        'brand',
        'design_name',
        'device_name',
        'device_type',
        'device_manufacturer',
        'model_id',
        'model_name',
        'os_build_fingerprint',
        'os_build_id',
        'os_internal_build_id',
        'os_name',
        'os_version',
        'platform_api_level',
        'product_name',
        'supported_cpu_architectures',
        'total_memory',
        'is_active',
        'not_active_at',
        'type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'bool',
        'supported_cpu_architectures' => 'json'
    ];
}
