<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * Фотографии карусели на публичной главной: storage/app/public/home.
 * Порядок задаётся файлом order.json (массив имён файлов).
 */
class HomeGallery
{
    public const ORDER_FILENAME = 'order.json';

    /**
     * @return array<int, string>
     */
    public static function allowedExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    }

    public static function directoryPath(): string
    {
        return storage_path('app/public/home');
    }

    /**
     * Максимальный номер N среди файлов вида «image N.ext» в каталоге (без учёта order.json).
     */
    public static function maxSequentialImageIndex(string $dir): int
    {
        if (! File::isDirectory($dir)) {
            return 0;
        }

        $max = 0;
        foreach (File::files($dir) as $file) {
            if ($file->getFilename() === self::ORDER_FILENAME) {
                continue;
            }
            if (preg_match('/^image (\d+)\./i', $file->getFilename(), $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $max;
    }

    /**
     * Коллекция объектов { url, filename } для отображения на главной.
     */
    public static function photos(): Collection
    {
        $dir = self::directoryPath();
        $allowed = self::allowedExtensions();

        if (! File::isDirectory($dir)) {
            return collect();
        }

        $filesByName = [];
        foreach (File::files($dir) as $file) {
            $name = $file->getFilename();
            if ($name === self::ORDER_FILENAME) {
                continue;
            }
            if (! in_array(strtolower($file->getExtension()), $allowed, true)) {
                continue;
            }
            $filesByName[$name] = $file;
        }

        $ordered = collect();
        $orderPath = $dir.DIRECTORY_SEPARATOR.self::ORDER_FILENAME;
        if (File::exists($orderPath)) {
            $list = json_decode(File::get($orderPath), true);
            if (is_array($list)) {
                foreach ($list as $name) {
                    if (! is_string($name)) {
                        continue;
                    }
                    $base = basename($name);
                    if (isset($filesByName[$base])) {
                        $ordered->push($filesByName[$base]);
                        unset($filesByName[$base]);
                    }
                }
            }
        }

        $rest = collect($filesByName)->sortByDesc(fn ($f) => $f->getMTime())->values();
        $ordered = $ordered->concat($rest);

        return $ordered->map(function ($file) {
            return (object) [
                'url' => asset('storage/home/'.$file->getFilename()),
                'filename' => $file->getFilename(),
            ];
        })->values();
    }

    /**
     * Сохранить порядок отображения (только существующие файлы с допустимым расширением).
     *
     * @param  array<int, string>  $filenames
     */
    public static function writeOrder(array $filenames): void
    {
        $dir = self::directoryPath();
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $allowed = self::allowedExtensions();
        $valid = [];
        foreach ($filenames as $name) {
            if (! is_string($name)) {
                continue;
            }
            $base = basename($name);
            if ($base === self::ORDER_FILENAME) {
                continue;
            }
            if (! self::filenameLooksSafe($base)) {
                continue;
            }
            $path = $dir.DIRECTORY_SEPARATOR.$base;
            if (! File::exists($path)) {
                continue;
            }
            $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
            if (! in_array($ext, $allowed, true)) {
                continue;
            }
            $valid[] = $base;
        }

        File::put(
            $dir.DIRECTORY_SEPARATOR.self::ORDER_FILENAME,
            json_encode($valid, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );
    }

    /**
     * Имя файла в каталоге home (в т.ч. с пробелами и кириллицей, без путей).
     */
    public static function filenameLooksSafe(string $base): bool
    {
        if ($base === '' || str_contains($base, '..')) {
            return false;
        }
        if (preg_match('#[/\\\\]#', $base)) {
            return false;
        }
        if (strlen($base) > 240) {
            return false;
        }

        return true;
    }

    public static function isAllowedFilename(string $filename): bool
    {
        $base = basename($filename);
        if ($base === self::ORDER_FILENAME) {
            return false;
        }
        if (! self::filenameLooksSafe($base)) {
            return false;
        }
        $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));

        return in_array($ext, self::allowedExtensions(), true);
    }
}
