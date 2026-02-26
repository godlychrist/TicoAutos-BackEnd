<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VehiclesController extends Controller
{
    public function index()
    {
        $vehicle = Vehicle::all();
        return response()->json($vehicle);
    }

    public function show($id)
    {
        $vehicle = Vehicle::with('user')->findOrFail($id);
        return response()->json($vehicle);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|numeric|min:1900|max:' . (date('Y') + 1),
            'price' => 'required|numeric',
            'status' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al crear el vehículo',
                'error' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('vehicles', 'public');
        } else {
            $imagePath = null;
        }

        $vehicle = Vehicle::create([
            'brand' => $request->brand,
            'model' => $request->model,
            'year' => $request->year,
            'price' => $request->price,
            'status' => $request->status,
            'image' => $imagePath,
            'user_id' => auth()->id() // Usamos el ID del token
        ]);

        return response()->json([
            'message' => 'Vehículo creado exitosamente',
            'vehicle' => $vehicle
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'brand' => 'string',
            'model' => 'string',
            'year' => 'numeric|min:1900|max:' . (date('Y') + 1),
            'price' => 'numeric',
            'status' => 'string',
            'image' => 'image|mimes:jpeg,png,jpg,webp',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error al editar el vehículo',
                'error' => $validator->errors()
            ], 422);
        }

        // Solo permitir editar si el vehículo le pertenece al usuario
        $vehicle = Vehicle::where('_id', $id)->where('user_id', auth()->id())->firstOrFail();

        $vehicle->update($request->only([
            'brand',
            'model',
            'year',
            'price',
            'status',
        ]));

        if ($request->hasFile('image')) {
            // Borrar imagen anterior si existe
            if ($vehicle->image) {
                Storage::disk('public')->delete($vehicle->image);
            }
            $imagePath = $request->file('image')->store('vehicles', 'public');
            $vehicle->update([
                'image' => $imagePath
            ]);
        }
        return response()->json([
            'message' => 'Vehículo editado exitosamente',
            'vehicle' => $vehicle
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        // Solo permitir borrar si el vehículo le pertenece al usuario
        $vehicle = Vehicle::where('_id', $id)->where('user_id', auth()->id())->firstOrFail();

        // Limpiar imagen del storage
        if ($vehicle->image) {
            Storage::disk('public')->delete($vehicle->image);
        }

        $vehicle->delete();
        return response()->json([
            'message' => 'Vehículo eliminado exitosamente',
            'vehicle' => $vehicle
        ], 200);
    }
}
