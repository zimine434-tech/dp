<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use Illuminate\Http\Request;

class SportController extends Controller
{
    /**
     * Display a listing of the sports.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'view' => 'nullable|in:list,cards',
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $view = (($validated['view'] ?? 'list') === 'cards') ? 'cards' : 'list';

        $query = Sport::with(['creator'])->latest();

        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $query->where('name', 'like', $like);
        }

        $sports = $query->paginate(10)->withQueryString();

        return view('sports.index', compact('sports', 'q', 'view'));
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
