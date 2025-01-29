<?php

namespace Common\Helpers;

use File;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\File as HttpFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class FileHelper
{
    public static function fileFromBase64(string $base64File, string $fileName = null): UploadedFile
    {
        // Get file data base64 string
        $fileData = base64_decode(Arr::last(explode(',', $base64File)));

        // Create temp file and get its absolute path
        $tempFile = tmpfile();
        $tempFilePath = stream_get_meta_data($tempFile)['uri'];

        // Save file data in file
        file_put_contents($tempFilePath, $fileData);

        $tempFileObject = new HttpFile($tempFilePath);
        $file = new UploadedFile(
            $tempFileObject->getPathname(),
            $fileName ?? $tempFileObject->getFilename(),
            $tempFileObject->getMimeType(),
            0,
            true, // Mark it as test, since the file isn't from real HTTP POST.
        );

        // Close this file after response is sent.
        // Closing the file will cause to remove it from temp director!
        app()->terminating(function () use ($tempFile) {
            fclose($tempFile);
        });

        // return UploadedFile object
        return $file;
    }

    /**
     * @param $base64
     *
     * @return false|string
     */
    public static function getFromBase64($base64)
    {
        $pattern = '/data:[A-Za-z0-9\-\/]+;base64,|;base64,/';
        $imageContent = preg_replace($pattern, '', $base64);

        return base64_decode($imageContent);
    }

    /**
     * @param $path
     * @param $file
     *
     * @return bool|int|void
     */
    public static function saveFromFile($path, $file)
    {
        if (isset($file['content']) && !empty($file['content'])) {
            return File::put($path, self::getFromBase64($file['content']));
        }
    }

    /**
     * @param $path
     * @param $ext
     * @param string $postfix
     *
     * @return string
     * @throws BindingResolutionException
     */
    public static function uniqueFilename($path, $ext, string $postfix = ''): string
    {
        $string = uniqid('', false) . $postfix . '.' . $ext;
        $name = Helper::public_path() . $path . DIRECTORY_SEPARATOR . $string;

        if (file_exists($name)) {
            return self::uniqueFilename($path, $ext);
        }

        return $string;
    }

    /**
     * Получение максимального размера загрузки файла из ini-файла
     */
    public static function getMaxUploadSize()
    {
        $postMaxSize = self::convertToBytes(ini_get('post_max_size')); //prod = 16mb
        $uploadMaxFilesize = self::convertToBytes(ini_get('upload_max_filesize')); //prod = 20mb

        // Берем минимальное значение из двух, так как оба влияют на размер загрузки
        return min($postMaxSize, $uploadMaxFilesize);
    }

    /**
     * Конвертация размера из строки (например, 2M) в байты
     */
    public static function convertToBytes($size)
    {
        $unit = strtoupper(substr($size, -1));
        $bytes = (int)$size;

        switch ($unit) {
            case 'K':
                $bytes *= 1024;
                break;
            case 'M':
                $bytes *= 1024 * 1024;
                break;
            case 'G':
                $bytes *= 1024 * 1024 * 1024;
                break;
        }

        return $bytes;
    }

    /**
     * Преобразование размера в читаемый формат
     */
    public static function humanReadableSize($size): string
    {
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 2) . ' GB';
        }

        if ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        }

        if ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        }

        return $size . ' bytes';
    }
}
