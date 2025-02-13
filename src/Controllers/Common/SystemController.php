<?php

namespace Common\Controllers\Common;

use App\Api\V1\Controllers\Common\BaseController;
use Common\Helpers\FileHelper;

class SystemController extends BaseController
{
    /**
     * @param $request
     * @return mixed
     */
    public function getIndex($request)
    {
        // Получение максимального размера из ini файла
        $maxFileSize = FileHelper::getMaxUploadSize();
        return $this->response()->json([
            'max_post_size' => $maxFileSize,
        ]);
    }
}