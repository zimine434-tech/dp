<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsImage;
use App\Support\NewsListingSort;
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
        $onlyMine = false;
        $newsStatus = $request->query('news_status', 'all');
        if (! in_array($newsStatus, ['all', 'published', 'draft'], true)) {
            $newsStatus = 'all';
        }

        $q = Str::limit(trim((string) $request->query('q', '')), 255, '');
        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $validatedDates = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $dateFrom = $validatedDates['date_from'] ?? null;
        $dateTo = $validatedDates['date_to'] ?? null;
        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $showPublished = in_array($newsStatus, ['all', 'published'], true);
        $showDraft = in_array($newsStatus, ['all', 'draft'], true);

        $cardsSortStack = NewsListingSort::parseStack($request);

        $publishedQuery = News::with(['creator', 'images'])
            ->where('status', 'Published');
        if ($onlyMine) {
            $publishedQuery->where('created_by', auth()->id());
        }
        $this->applyNewsNameSearch($publishedQuery, $q);
        $this->applyNewsDateRange($publishedQuery, $dateFrom, $dateTo);
        NewsListingSort::applyToQuery($publishedQuery, $cardsSortStack);

        $draftQuery = News::with(['creator', 'images'])
            ->where('status', 'Draft');
        if ($onlyMine) {
            $draftQuery->where('created_by', auth()->id());
        }
        $this->applyNewsNameSearch($draftQuery, $q);
        $this->applyNewsDateRange($draftQuery, $dateFrom, $dateTo);
        NewsListingSort::applyToQuery($draftQuery, $cardsSortStack);

        $publishedNews = $showPublished
            ? (clone $publishedQuery)->paginate($perPage, ['*'], 'published')->withQueryString()
            : null;

        $draftNews = $showDraft
            ? (clone $draftQuery)->paginate($perPage, ['*'], 'draft')->withQueryString()
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

            return response()->json($this->newsListingAjaxPayload(
                $onlyMine,
                $q,
                $dateFrom,
                $dateTo,
                $newsStatus,
                $perPage,
                $cardsSortStack,
                $publishedHtml,
                $publishedPagination,
                $draftHtml,
                $draftPagination,
                $publishedTotal,
                $draftTotal,
                $showPublished,
                $showDraft,
            ));
        }

        return view('news.index', compact(
            'publishedNews',
            'draftNews',
            'newsStatus',
            'q',
            'dateFrom',
            'dateTo',
            'perPage',
            'showPublished',
            'showDraft',
            'publishedTotal',
            'draftTotal',
            'onlyMine',
            'cardsSortStack',
        ));
    }

    /**
     * Страница "Мои новости" (только созданные текущим преподавателем).
     */
    public function myIndex(Request $request)
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'teacher') {
            abort(403);
        }

        // Флаг используется в index для фильтрации и в blade для заголовка/ссылок.
        $request->merge(['__only_mine' => 1]);

        // Локально переиспользуем index-логику, но с включённым фильтром.
        $newsStatus = $request->query('news_status', 'all');
        if (! in_array($newsStatus, ['all', 'published', 'draft'], true)) {
            $newsStatus = 'all';
        }

        $q = Str::limit(trim((string) $request->query('q', '')), 255, '');
        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $validatedDates = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $dateFrom = $validatedDates['date_from'] ?? null;
        $dateTo = $validatedDates['date_to'] ?? null;
        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $showPublished = in_array($newsStatus, ['all', 'published'], true);
        $showDraft = in_array($newsStatus, ['all', 'draft'], true);

        $cardsSortStack = NewsListingSort::parseStack($request);

        $publishedQuery = News::with(['creator', 'images'])
            ->where('status', 'Published')
            ->where('created_by', $user->id);
        $this->applyNewsNameSearch($publishedQuery, $q);
        $this->applyNewsDateRange($publishedQuery, $dateFrom, $dateTo);
        NewsListingSort::applyToQuery($publishedQuery, $cardsSortStack);

        $draftQuery = News::with(['creator', 'images'])
            ->where('status', 'Draft')
            ->where('created_by', $user->id);
        $this->applyNewsNameSearch($draftQuery, $q);
        $this->applyNewsDateRange($draftQuery, $dateFrom, $dateTo);
        NewsListingSort::applyToQuery($draftQuery, $cardsSortStack);

        $publishedNews = $showPublished
            ? (clone $publishedQuery)->paginate($perPage, ['*'], 'published')->withQueryString()
            : null;

        $draftNews = $showDraft
            ? (clone $draftQuery)->paginate($perPage, ['*'], 'draft')->withQueryString()
            : null;

        $publishedTotal = (clone $publishedQuery)->count();
        $draftTotal = (clone $draftQuery)->count();

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

            return response()->json($this->newsListingAjaxPayload(
                true,
                $q,
                $dateFrom,
                $dateTo,
                $newsStatus,
                $perPage,
                $cardsSortStack,
                $publishedHtml,
                $publishedPagination,
                $draftHtml,
                $draftPagination,
                $publishedTotal,
                $draftTotal,
                $showPublished,
                $showDraft,
            ));
        }

        $onlyMine = true;

        return view('news.index', compact(
            'publishedNews',
            'draftNews',
            'newsStatus',
            'q',
            'dateFrom',
            'dateTo',
            'perPage',
            'showPublished',
            'showDraft',
            'publishedTotal',
            'draftTotal',
            'onlyMine',
            'cardsSortStack',
        ));
    }

    /**
     * @param  array<int, array{field: string, order: string}>  $cardsSortStack
     * @return array<string, mixed>
     */
    protected function newsListingAjaxPayload(
        bool $onlyMine,
        string $q,
        ?string $dateFrom,
        ?string $dateTo,
        string $newsStatus,
        int $perPage,
        array $cardsSortStack,
        string $publishedHtml,
        string $publishedPagination,
        string $draftHtml,
        string $draftPagination,
        int $publishedTotal,
        int $draftTotal,
        bool $showPublished,
        bool $showDraft,
    ): array {
        return [
            'published' => [
                'html' => $publishedHtml,
                'pagination' => $publishedPagination,
            ],
            'draft' => [
                'html' => $draftHtml,
                'pagination' => $draftPagination,
            ],
            'sort_bar_html' => $this->renderNewsSortBarHtml(
                $onlyMine,
                $q,
                $dateFrom,
                $dateTo,
                $newsStatus,
                $perPage,
                $cardsSortStack,
            ),
            'meta' => [
                'published_total' => $publishedTotal,
                'draft_total' => $draftTotal,
                'show_published' => $showPublished,
                'show_draft' => $showDraft,
            ],
        ];
    }

    /**
     * @param  array<int, array{field: string, order: string}>  $cardsSortStack
     */
    protected function renderNewsSortBarHtml(
        bool $onlyMine,
        string $q,
        ?string $dateFrom,
        ?string $dateTo,
        string $newsStatus,
        int $perPage,
        array $cardsSortStack,
    ): string {
        $baseListingParams = array_filter([
            'q' => $q !== '' ? $q : null,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'news_status' => $newsStatus !== 'all' ? $newsStatus : null,
            'per_page' => $perPage !== 10 ? (string) $perPage : null,
        ], static fn ($value) => $value !== null && $value !== '');

        return view('news.partials.news-sort-bar', [
            'listingRoute' => $onlyMine ? 'news.my' : 'news.index',
            'baseListingParams' => $baseListingParams,
            'cardsSortStack' => $cardsSortStack,
        ])->render();
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
        $query->where(function ($builder) use ($like) {
            $builder->where('name', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    /**
     * @param  Builder<News>  $query
     */
    protected function applyNewsDateRange(Builder $query, ?string $dateFrom, ?string $dateTo): void
    {
        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }
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
        $listingFilters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $listingFilters['q'] = Str::limit(trim((string) ($listingFilters['q'] ?? '')), 255, '');

        $perPage = (int) $request->query('per_page', 25);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $dateFrom = $listingFilters['date_from'] ?? null;
        $dateTo = $listingFilters['date_to'] ?? null;
        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
            $listingFilters['date_from'] = $dateFrom;
            $listingFilters['date_to'] = $dateTo;
        }

        $q = $listingFilters['q'];

        $cardsSortStack = NewsListingSort::parseStack($request);

        $publishedQuery = News::with(['creator', 'images'])
            ->where('status', 'Published');
        $this->applyNewsNameSearch($publishedQuery, $q);
        $this->applyNewsDateRange($publishedQuery, $dateFrom, $dateTo);
        NewsListingSort::applyToQuery($publishedQuery, $cardsSortStack);

        $publishedNews = $publishedQuery->paginate($perPage)->withQueryString();

        return view('news.student.index', compact('publishedNews', 'listingFilters', 'perPage', 'cardsSortStack'));
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
