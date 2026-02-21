<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::all();
        return response()->json($vehicles);
    }

    public function store(Request $request)
    {
        \Log::info('Datos recibidos en store:', $request->all());
        \Log::info('Archivos recibidos:', $request->allFiles());
        $validator = Validator::make($request->all(), [
            'brand' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|numeric',
            'price' => 'required|numeric',
            'status' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            \Log::error('Validación de vehículo fallida:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('vehicles', 'public');
                $imageUrl = Storage::url($path);
            }

            $vehicle = Vehicle::create([
                'brand' => $request->brand,
                'model' => $request->model,
                'year' => $request->year,
                'price' => $request->price,
                'status' => $request->status,
                'image_url' => $imageUrl,
                'user_id' => $request->user_id
            ]);

            return response()->json([
                'message' => 'Vehículo creado con éxito',
                'vehicle' => $vehicle
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error al crear vehículo: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear el vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'brand' => 'sometimes|string',
            'model' => 'sometimes|string',
            'year' => 'sometimes|numeric',
            'price' => 'sometimes|numeric',
            'status' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->update($request->all());

            return response()->json([
                'message' => 'Vehículo actualizado con éxito',
                'vehicle' => $vehicle
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al actualizar vehículo: ' . $e->getMessage());
            return response()->json(['message' => 'Error al actualizar el vehículo'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);

            // Borrar la imagen física si existe
            if ($vehicle->image_url) {
                $path = str_replace('/storage/', '', $vehicle->image_url);
                Storage::disk('public')->delete($path);
            }

            $vehicle->delete();

            return response()->json(['message' => 'Vehículo eliminado con éxito']);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar vehículo: ' . $e->getMessage());
            return response()->json(['message' => 'Error al eliminar el vehículo'], 500);
        }
    }
}
