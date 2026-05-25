<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    /**
     * Display a listing of the news.
     */
    public function index(Request $request)
    {
        $newsStatus = $request->query('news_status', 'all');
        if (! in_array($newsStatus, ['all', 'published', 'draft'], true)) {
            $newsStatus = 'all';
        }

        $q = Str::limit(trim((string) $request->query('q', '')), 255, '');

        $showPublished = in_array($newsStatus, ['all', 'published'], true);
        $showDraft = in_array($newsStatus, ['all', 'draft'], true);

        $publishedQuery = News::with(['creator', 'images'])
            ->where('status', 'Published')
            ->latest('date');
        $this->applyNewsNameSearch($publishedQuery, $q);

        $draftQuery = News::with(['creator', 'images'])
            ->where('status', 'Draft')
            ->latest('date');
        $this->applyNewsNameSearch($draftQuery, $q);

        $publishedNews = $showPublished
            ? (clone $publishedQuery)->paginate(4, ['*'], 'published')->withQueryString()
            : null;

        $draftNews = $showDraft
            ? (clone $draftQuery)->paginate(4, ['*'], 'draft')->withQueryString()
            : null;

        $publishedTotal = (clone $publishedQuery)->count();
        $draftTotal = (clone $draftQuery)->count();

        // Если это AJAX-запрос, возвращаем JSON
        if ($request->ajax() || $request->has('ajax')) {
            $publishedHtml = '';
            $publishedPagination = '';

            if ($showPublished) {
                if ($publishedNews->count() > 0) {
                    $publishedHtml = view('news.partials.news-grid', ['news' => $publishedNews, 'type' => 'published'])->render();
                    if ($publishedNews->hasPages()) {
                        $publishedPagination = $publishedNews->links()->render();
                    }
                } else {
                    $publishedHtml = view('news.partials.news-empty-teacher', [
                        'variant' => 'published',
                        'hasSearch' => $q !== '',
                    ])->render();
                }
            }

            $draftHtml = '';
            $draftPagination = '';

            if ($showDraft) {
                if ($draftNews->count() > 0) {
                    $draftHtml = view('news.partials.news-grid', ['news' => $draftNews, 'type' => 'draft'])->render();
                    if ($draftNews->hasPages()) {
                        $draftPagination = $draftNews->links()->render();
                    }
                } else {
                    $draftHtml = view('news.partials.news-empty-teacher', [
                        'variant' => 'draft',
                        'hasSearch' => $q !== '',
                    ])->render();
                }
            }

            return response()->json([
                'published' => [
                    'html' => $publishedHtml,
                    'pagination' => $publishedPagination,
                ],
                'draft' => [
                    'html' => $draftHtml,
                    'pagination' => $draftPagination,
                ],
                'meta' => [
                    'published_total' => $publishedTotal,
                    'draft_total' => $draftTotal,
                    'show_published' => $showPublished,
                    'show_draft' => $showDraft,
                ],
            ]);
        }

        return view('news.index', compact(
            'publishedNews',
            'draftNews',
            'newsStatus',
            'q',
            'showPublished',
            'showDraft',
            'publishedTotal',
            'draftTotal',
        ));
    }

    /**
     * @param  Builder<News>  $query
     */
    protected function applyNewsNameSearch(Builder $query, string $q): void
    {
        if ($q === '') {
            return;
        }
        $like = '%'.addcslashes($q, '%_\\').'%';
        $query->where('name', 'like', $like);
    }

    /**
     * Show the form for creating a new news.
     */
    public function create()
    {
        return view('news.create');
    }

    /**
     * Store a newly created news in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'images' => 'nullable|array|max:20',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ], [
            'name.required' => 'Название новости обязательно для заполнения.',
            'name.max' => 'Название новости не может быть длиннее 200 символов.',
            'images.*.image' => 'Каждый файл должен быть изображением.',
            'images.*.max' => 'Размер каждого изображения не более 5 МБ.',
            'images.max' => 'Не более 20 фотографий за раз.',
        ]);

        // Автоматически устанавливаем сегодняшнюю дату
        $todayDate = now()->toDateString();

        // Проверяем совпадение по дате и названию
        $existingNews = News::where('date', $todayDate)
            ->where('name', $validated['name'])
            ->first();

        if ($existingNews) {
            return redirect()->route('news.create')
                ->withErrors(['name' => 'Такая новость уже существует с сегодняшней датой.'])
                ->withInput();
        }

        $news = News::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'date' => $todayDate,
            'status' => 'Draft',
            'created_by' => auth()->id(),
        ]);

        foreach ($request->file('images', []) as $file) {
            if (!$file->isValid()) {
                continue;
            }
            $this->attachNewsImageFromUpload($news, $file);
        }

        return redirect()->route('news.index')
            ->with('success', 'Новость успешно создана!');
    }

    /**
     * Display the specified news.
     */
    public function show(News $news)
    {
        $news->load(['creator', 'images']);
        $user = auth()->user();

        // Если студент пытается просмотреть черновик, перенаправляем на список новостей
        if ($user->role === 'student' && $news->status !== 'Published') {
            return redirect()->route('news.index')
                ->with('error', 'Эта новость недоступна для просмотра.');
        }

        // Выбираем представление в зависимости от роли
        $view = $user->role === 'teacher' ? 'news.show' : 'news.student.show';

        return view($view, compact('news'));
    }

    /**
     * Show the form for editing the specified news.
     */
    public function edit(News $news)
    {
        $news->load('images');

        return view('news.edit', compact('news'));
    }

    /**
     * Update the specified news in storage.
     */
    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'images' => 'nullable|array|max:20',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp,gif|max:5120',
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => ['integer', Rule::exists('news_images', 'id')->where('news_id', $news->id)],
        ], [
            'name.required' => 'Название новости обязательно для заполнения.',
            'name.max' => 'Название новости не может быть длиннее 200 символов.',
            'images.*.image' => 'Каждый файл должен быть изображением.',
            'images.*.max' => 'Размер каждого изображения не более 5 МБ.',
            'images.max' => 'Не более 20 фотографий за раз.',
        ]);

        // Автоматически устанавливаем сегодняшнюю дату
        $todayDate = now()->toDateString();

        // Проверяем совпадение по дате и названию (исключаем текущую новость)
        $existingNews = News::where('date', $todayDate)
            ->where('name', $validated['name'])
            ->where('id', '!=', $news->id)
            ->first();

        if ($existingNews) {
            return redirect()->route('news.edit', $news)
                ->withErrors(['name' => 'Такая новость уже существует с сегодняшней датой.'])
                ->withInput();
        }

        if (!empty($validated['remove_image_ids'])) {
            $toRemove = $news->images()->whereIn('id', $validated['remove_image_ids'])->get();
            foreach ($toRemove as $img) {
                $this->deletePublicNewsImageFile($img->path);
                $img->delete();
            }
        }

        foreach ($request->file('images', []) as $file) {
            if (!$file->isValid()) {
                continue;
            }
            $this->attachNewsImageFromUpload($news, $file);
        }

        $news->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'date' => $todayDate,
        ]);

        return redirect()->route('news.index')
            ->with('success', 'Новость успешно обновлена!');
    }

    /**
     * Publish the specified news.
     */
    public function publish(News $news)
    {
        $news->update(['status' => 'Published']);

        return redirect()->route('news.index')
            ->with('success', 'Новость успешно опубликована!');
    }

    /**
     * Remove the specified news from storage.
     */
    public function destroy(News $news)
    {
        foreach ($news->images as $image) {
            $this->deletePublicNewsImageFile($image->path);
        }

        $news->delete();

        return redirect()->route('news.index')
            ->with('success', 'Новость успешно удалена!');
    }

    /**
     * Display a listing of the published news for students.
     */
    public function indexStudent(Request $request)
    {
        $publishedNews = News::with(['creator', 'images'])
            ->where('status', 'Published')
            ->latest('date')
            ->paginate(12);

        // Если это AJAX-запрос, возвращаем JSON
        if ($request->ajax() || $request->has('ajax')) {
            $html = '';
            $pagination = '';

            if ($publishedNews->count() > 0) {
                $html = view('news.partials.news-grid', ['news' => $publishedNews, 'type' => 'student'])->render();
                if ($publishedNews->hasPages()) {
                    $pagination = $publishedNews->links('pagination::tailwind')->render();
                }
            } else {
                $html = '<div class="px-6 py-12 text-center"><svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg><p class="mt-2 text-sm text-gray-500">Нет опубликованных новостей</p></div>';
            }

            return response()->json([
                'html' => $html,
                'pagination' => $pagination,
            ]);
        }

        return view('news.student.index', compact('publishedNews'));
    }

    /**
     * Сохраняет файл как news/image_{id записи}.{расширение}, где id — первичный ключ news_images.
     */
    private function attachNewsImageFromUpload(News $news, UploadedFile $file): void
    {
        $ext = $this->normalizeUploadedImageExtension($file);
        $disk = Storage::disk('public');
        $tempRelative = $file->storeAs('news', '_tmp_'.Str::uuid()->toString().'.'.$ext, 'public');

        /** @var NewsImage $image */
        $image = $news->images()->create([
            'path' => $tempRelative,
        ]);

        $finalRelative = 'news/image_'.$image->id.'.'.$ext;

        try {
            if ($disk->exists($finalRelative)) {
                $disk->delete($finalRelative);
            }
            $disk->move($tempRelative, $finalRelative);
            $image->update(['path' => $finalRelative]);
        } catch (\Throwable $e) {
            $image->delete();
            if ($disk->exists($tempRelative)) {
                $disk->delete($tempRelative);
            }
            throw $e;
        }
    }

    private function normalizeUploadedImageExtension(UploadedFile $file): string
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (! in_array($ext, $allowed, true)) {
            $ext = strtolower((string) $file->guessExtension());
        }
        if (! in_array($ext, $allowed, true)) {
            $ext = 'jpg';
        }

        return $ext;
    }

    /**
     * Удаляет файл изображения с public-диска.
     */
    private function deletePublicNewsImageFile(string $storedPath): void
    {
        $disk = Storage::disk('public');
        $normalized = str_replace('\\', '/', ltrim($storedPath, '/'));
        if ($disk->exists($normalized)) {
            $disk->delete($normalized);
        }
    }
}
