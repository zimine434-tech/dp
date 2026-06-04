<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class UploadedFileErrors
{
    /**
     * @param  array<int, UploadedFile|null>|null  $files
     * @return array{index: int, file: UploadedFile}|null
     */
    public static function firstInvalid(?array $files): ?array
    {
        if (! is_array($files)) {
            return null;
        }

        foreach ($files as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            if (! $file->isValid()) {
                return ['index' => (int) $index, 'file' => $file];
            }
        }

        return null;
    }

    /**
     * @param  array<int, UploadedFile|null>|null  $files
     */
    public static function firstMissing(?array $files): ?int
    {
        if (! is_array($files)) {
            return null;
        }

        foreach ($files as $index => $file) {
            if ($file === null) {
                return (int) $index;
            }
        }

        return null;
    }

    public static function messageFor(UploadedFile $file, int $humanIndex = 1): string
    {
        $phpUploadMax = (string) ini_get('upload_max_filesize');
        $phpPostMax = (string) ini_get('post_max_size');
        $prefix = $humanIndex > 0 ? "Файл №{$humanIndex}: " : '';

        $detail = match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'превышен лимит размера (upload_max_filesize или MAX_FILE_SIZE).',
            UPLOAD_ERR_PARTIAL => 'файл загружен не полностью — повторите загрузку.',
            UPLOAD_ERR_NO_FILE => 'файл не передан.',
            UPLOAD_ERR_NO_TMP_DIR => 'на сервере нет временной папки для загрузки.',
            UPLOAD_ERR_CANT_WRITE => 'не удалось записать файл на диск.',
            UPLOAD_ERR_EXTENSION => 'загрузка остановлена расширением PHP.',
            default => 'не удалось принять файл.',
        };

        return $prefix.$detail.' Лимиты PHP: upload_max_filesize='.$phpUploadMax.', post_max_size='.$phpPostMax.'.';
    }

    public static function missingSlotMessage(int $zeroBasedIndex): string
    {
        $humanIndex = $zeroBasedIndex + 1;
        $phpPostMax = (string) ini_get('post_max_size');

        return "Файл №{$humanIndex} не получен сервером. Загрузите меньше файлов за раз, уменьшите размер фото или отправляйте по одному. Лимит post_max_size={$phpPostMax}.";
    }

    public static function phpLimitsHint(): string
    {
        return 'upload_max_filesize='.(string) ini_get('upload_max_filesize')
            .', post_max_size='.(string) ini_get('post_max_size');
    }
}
