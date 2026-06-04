<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Support\SportListingSort;
use Illuminate\Http\Request;

class SportController extends Controller
{
    /**
     * Display a listing of the sports.
     */
    public function index(Request $request)
    {
        return view('sports.index', $this->resolveSportsListing($request, false));
    }

    /**
     * Страница "Мои виды спорта" (только созданные текущим преподавателем).
     */
    public function myIndex(Request $request)
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'teacher') {
            abort(403);
        }

        return view('sports.index', $this->resolveSportsListing($request, true));
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveSportsListing(Request $request, bool $onlyMine): array
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'view' => 'nullable|in:list,cards',
            'per_page' => 'nullable|integer',
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $view = (($validated['view'] ?? 'list') === 'cards') ? 'cards' : 'list';
        $perPage = (int) ($validated['per_page'] ?? 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $cardsSortStack = SportListingSort::parseStack($request, SportListingSort::PREFIX_CARDS);
        $listSortStack = SportListingSort::parseStack($request, SportListingSort::PREFIX_LIST);
        $activeSortStack = $view === 'cards'
            ? $cardsSortStack
            : SportListingSort::normalizeListStack($listSortStack);

        $query = Sport::with(['creator']);

        if ($onlyMine) {
            $query->where('created_by', auth()->id());
        }

        if ($q !== '') {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $query->where('name', 'like', $like);
        }

        SportListingSort::applyToQuery($query, $activeSortStack);

        $sports = $query->paginate($perPage)->withQueryString();

        return compact(
            'sports',
            'q',
            'view',
            'perPage',
            'onlyMine',
            'cardsSortStack',
            'listSortStack',
        );
    }

    /**
     * Show the form for creating a new sport.
     */
    public function create()
    {
        return view('sports.create');

    }

    /**
     * Store a newly created sport in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Название вида спорта обязательно для заполнения.',
            'name.max' => 'Название вида спорта не может быть длиннее 100 символов.',
        ]);

        $existingSportByName = Sport::where('name', $validated['name'])->first();
        if ($existingSportByName) {
            return redirect()->route('sports.create')
                ->withErrors(['name' => 'Вид спорта с таким названием уже существует.'])
                ->withInput();
        }

        Sport::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('sports.index')
            ->with('success', 'Спорт успешно создан!');
    }

    /**
     * Display the specified sport.
     */
    public function show(Sport $sport)
    {
        $sport->load(['creator', 'teams']);

        return view('sports.show', compact('sport'));
    }

    /**
     * Show the form for editing the specified sport.
     */
    public function edit(Sport $sport)
    {
        return view('sports.edit', compact('sport'));
    }

    /**
     * Update the specified sport in storage.
     */
    public function update(Request $request, Sport $sport)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Название вида спорта обязательно для заполнения.',
            'name.max' => 'Название вида спорта не может быть длиннее 100 символов.',
        ]);

        $existingSportByName = Sport::where('name', $validated['name'])
            ->where('id', '!=', $sport->id)
            ->first();
        if ($existingSportByName) {
            return redirect()->route('sports.edit', $sport)
                ->withErrors(['name' => 'Вид спорта с таким названием уже существует.'])
                ->withInput();
        }

        $sport->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('sports.index')
            ->with('success', 'Спорт успешно обновлен!');
    }

    /**
     * Remove the specified sport from storage.
     */
    public function destroy(Sport $sport)
    {
        $sport->delete();

        return redirect()->route('sports.index')
            ->with('success', 'Спорт успешно удален!');
    }
}
