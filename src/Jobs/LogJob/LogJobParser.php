<?php

namespace Common\Jobs\LogJob;

use Common\Models\BaseModel;

/**
 * @property $user_id
 * @property $broker_name
 * @property $comment
 * @property $path_file
 */
class LogJobParser extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'log_job_parsers';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'job_name',
        'comment',
        'path_file',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'job_name' => 'string',
        'comment' => 'string',
        'path_file' => 'string',
    ];
}
