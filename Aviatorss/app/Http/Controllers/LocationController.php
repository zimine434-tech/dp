<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:200|unique:locations,location',
            'organizer' => 'nullable|string|max:100',
        ], [
            'location.required' => 'Поле локации обязательно для заполнения.',
            'location.max' => 'Название локации не может быть длиннее 200 символов.',
            'location.unique' => 'Локация с таким названием уже существует.',
            'organizer.max' => 'Название организатора не может быть длиннее 100 символов.',
        ]);

        $location = Location::create($validated);

        return response()->json([
            'success' => true,
            'location' => [
                'id' => $location->id,
                'location' => $location->location,
                'organizer' => $location->organizer,
            ],
            'message' => 'Локация успешно создана!'
        ]);
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:200|unique:locations,location,' . $location->id,
            'organizer' => 'nullable|string|max:100',
        ], [
            'location.required' => 'Поле локации обязательно для заполнения.',
            'location.max' => 'Название локации не может быть длиннее 200 символов.',
            'location.unique' => 'Локация с таким названием уже существует.',
            'organizer.max' => 'Название организатора не может быть длиннее 100 символов.',
        ]);

        $location->update($validated);

        return response()->json([
            'success' => true,
            'location' => [
                'id' => $location->id,
                'location' => $location->location,
                'organizer' => $location->organizer,
            ],
            'message' => 'Локация успешно обновлена!'
        ]);
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy(Location $location)
    {
        $location->delete();

        return response()->json([
            'success' => true,
            'message' => 'Локация успешно удалена!'
        ]);
    }
}

