<?php

namespace App\Http\Controllers;

use App\Models\CompetitionCategory;
use Illuminate\Http\Request;

class CompetitionCategoryController extends Controller
{
    public function index()
    {
        $categories = CompetitionCategory::orderBy('name_category')->get();

        return view('competition-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_category' => 'required|string|max:100|unique:competition_categories,name_category',
            'description' => 'nullable|string|max:45',
        ], [
            'name_category.required' => 'Название категории обязательно для заполнения.',
            'name_category.max' => 'Название категории не может быть длиннее 100 символов.',
            'name_category.unique' => 'Категория с таким названием уже существует.',
            'description.max' => 'Описание не может быть длиннее 45 символов.',
        ]);

        $category = CompetitionCategory::create($validated);

        return response()->json([
            'success' => true,
            'category' => [
                'id' => $category->id,
                'name_category' => $category->name_category,
                'description' => $category->description,
            ],
            'message' => 'Категория успешно создана!',
        ]);
    }

    public function update(Request $request, CompetitionCategory $category)
    {
        $validated = $request->validate([
            'name_category' => 'required|string|max:100|unique:competition_categories,name_category,' . $category->id,
            'description' => 'nullable|string|max:45',
        ], [
            'name_category.required' => 'Название категории обязательно для заполнения.',
            'name_category.max' => 'Название категории не может быть длиннее 100 символов.',
            'name_category.unique' => 'Категория с таким названием уже существует.',
            'description.max' => 'Описание не может быть длиннее 45 символов.',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'category' => [
                'id' => $category->id,
                'name_category' => $category->name_category,
                'description' => $category->description,
            ],
            'message' => 'Категория успешно обновлена!',
        ]);
    }

    public function destroy(CompetitionCategory $category)
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Категория успешно удалена!',
        ]);
    }
}
