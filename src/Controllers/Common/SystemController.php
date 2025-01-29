<?php

namespace Common\Controllers\Common;

use App\Api\V1\Controllers\Common\BaseController;
use App\Helpers\FileHelper;
use Illuminate\Http\Response;

class SystemController extends BaseController
{
    /**
     * @return Response
     */
    public function getSystemSettings()
    {
        // Получение максимального размера из ini файла
        $maxFileSize = FileHelper::getMaxUploadSize();
        return $this->response()->json([
            'max_post_size' => $maxFileSize,
        ]);
    }
}