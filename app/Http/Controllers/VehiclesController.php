<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VehiclesController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query();

        // Filtro por búsqueda de texto (Marca o Modelo)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('model', 'like', '%' . $search . '%')
                    ->orWhere('brand', 'like', '%' . $search . '%');
            });
        }

        // Filtro por Marca exacta
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Filtro por Estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por Año Mínimo
        if ($request->filled('year_min')) {
            $query->where('year', '>=', (int) $request->year_min);
        }

        // Filtro por Año Máximo
        if ($request->filled('year_max')) {
            $query->where('year', '<=', (int) $request->year_max);
        }

        // Filtro por Rango de Precio
        if ($request->filled('price_range')) {
            $range = explode('-', $request->price_range);
            if (count($range) === 2) {
                $query->where('price', '>=', (int) $range[0])
                    ->where('price', '<=', (int) $range[1]);
            }
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
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
