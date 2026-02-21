<?php
namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Usaremos DB directamente
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;


class VehiclesController extends BaseController {

    public function index() {
        $vehicles = DB::connection('mongodb')
        ->collection('vehicles')
        ->get();
        return response()->json($vehicles);
    }

    public function store(Request $request) {

        $request->validate([
            'brand' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:2025',
            'model' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'status' => 'required|string|in:available,sold',
            'image' => 'required|image',
            'user_id' => 'required|string'
        ]);

        $path = null;   
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('vehiculos', 'public');
        }

        $vehicle = DB::connection('mongodb')
        ->collection('vehicles')
        ->insert([
            'brand' => $request->brand,
            'year' => $request->year,
            'model' => $request->model,
            'price' => $request->price,
            'status' => $request->status,
            'image' => $path,
            'user_id' => $request->user_id,

        ]);

        return response()->json(['message' => 'Vehiculo agregado', 'vehicle' => $vehicle], 201);

    }

}