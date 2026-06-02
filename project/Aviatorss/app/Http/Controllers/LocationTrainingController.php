<?php

namespace App\Http\Controllers;

use App\Models\LocationTraining;
use Illuminate\Http\Request;

class LocationTrainingController extends Controller
{
    /**
     * Store a newly created location training in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:100|unique:locations_training,location',
            'description' => 'nullable|string|max:200',
        ], [
            'location.unique' => 'Локация с таким названием уже существует.',
            'location.required' => 'Поле локации обязательно для заполнения.',
            'location.max' => 'Название локации не может быть длиннее 100 символов.',
        ]);

        $location = LocationTraining::create($validated);

        return response()->json([
            'success' => true,
            'location' => [
                'id' => $location->id,
                'location' => $location->location,
                'description' => $location->description,
            ],
            'message' => 'Локация успешно создана!'
        ]);
    }

    /**
     * Update the specified location training in storage.
     */
    public function update(Request $request, LocationTraining $locationTraining)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:100|unique:locations_training,location,' . $locationTraining->id,
            'description' => 'nullable|string|max:200',
        ], [
            'location.unique' => 'Локация с таким названием уже существует.',
            'location.required' => 'Поле локации обязательно для заполнения.',
            'location.max' => 'Название локации не может быть длиннее 100 символов.',
        ]);

        $locationTraining->update($validated);

        return response()->json([
            'success' => true,
            'location' => [
                'id' => $locationTraining->id,
                'location' => $locationTraining->location,
                'description' => $locationTraining->description,
            ],
            'message' => 'Локация успешно обновлена!'
        ]);
    }

    /**
     * Remove the specified location training from storage.
     */
    public function destroy(LocationTraining $locationTraining)
    {
        $locationTraining->delete();

        return response()->json([
            'success' => true,
            'message' => 'Локация успешно удалена!'
        ]);
    }
}

