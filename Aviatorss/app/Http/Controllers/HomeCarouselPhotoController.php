<?php

namespace App\Http\Controllers;

use App\Support\HomeGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class HomeCarouselPhotoController extends Controller
{
    public function index()
    {
        $photos = HomeGallery::photos();

        return view('home-carousel.manage', compact('photos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'file|max:10240|mimes:jpeg,jpg,png,webp,gif',
        ], [
            'photos.required' => 'Выберите хотя бы один файл.',
            'photos.array' => 'Ошибка отправки формы: файлы не переданы.',
            'photos.min' => 'Выберите хотя бы один файл.',
            'photos.*.uploaded' => 'Превышен размер файла.',
            'photos.*.max' => 'Превышен размер файла.',
            'photos.*.mimes' => 'Допустимы только JPEG, PNG, WebP и GIF.',
        ]);

        $uploaded = $request->file('photos');
        $files = [];
        if ($uploaded === null) {
            $files = [];
        } elseif (is_array($uploaded)) {
            $files = $uploaded;
        } else {
            $files = [$uploaded];
        }

        $dir = HomeGallery::directoryPath();
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $orderPath = $dir.DIRECTORY_SEPARATOR.HomeGallery::ORDER_FILENAME;
        $list = [];
        if (File::exists($orderPath)) {
            $list = json_decode(File::get($orderPath), true) ?: [];
        }
        if (! is_array($list)) {
            $list = [];
        }
        $list = array_values(array_filter($list, 'is_string'));

        $nextIndex = HomeGallery::maxSequentialImageIndex($dir) + 1;
        $added = 0;
        foreach ($files as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');
            $ext = match ($ext) {
                'jpeg' => 'jpg',
                default => $ext,
            };
            if (! in_array($ext, HomeGallery::allowedExtensions(), true)) {
                continue;
            }
            $name = 'image '.$nextIndex.'.'.$ext;
            $stored = $file->storeAs('home', $name, 'public');
            if ($stored === false) {
                continue;
            }
            $list[] = $name;
            $added++;
            $nextIndex++;
        }

        if ($added === 0) {
            return redirect()->route('home-carousel-photos.index')->withErrors([
                'photos' => 'Файлы не сохранены. Проверьте размер (до 10 МБ и лимиты PHP), формат изображения и права на каталог storage/app/public/home.',
            ]);
        }

        HomeGallery::writeOrder($list);

        return redirect()->route('home-carousel-photos.index')->with(
            'success',
            $added === 1 ? 'Добавлено 1 фото.' : "Добавлено фото: {$added}."
        );
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'string',
        ]);

        HomeGallery::writeOrder($request->input('order'));

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'filename' => 'required|string|max:240',
        ]);

        $filename = basename($request->input('filename', ''));
        if (! HomeGallery::isAllowedFilename($filename)) {
            abort(404);
        }

        $path = HomeGallery::directoryPath().DIRECTORY_SEPARATOR.$filename;
        if (! File::exists($path) || ! File::isFile($path)) {
            abort(404);
        }

        Storage::disk('public')->delete('home/'.$filename);

        $orderPath = HomeGallery::directoryPath().DIRECTORY_SEPARATOR.HomeGallery::ORDER_FILENAME;
        $list = [];
        if (File::exists($orderPath)) {
            $list = json_decode(File::get($orderPath), true) ?: [];
        }
        $list = array_values(array_filter(is_array($list) ? $list : [], fn ($n) => is_string($n) && $n !== $filename));

        HomeGallery::writeOrder($list);

        return redirect()->route('home-carousel-photos.index')->with('success', 'Фотография удалена.');
    }
}
