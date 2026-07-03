<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    //

      public function index()
    {
        $tables = Table::latest()->get();

        return response()->json([
            'data' => $tables,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_number' => 'required|string|unique:tables,table_number',
            'qr_code' => 'nullable|string',
            'status' => 'nullable|string|in:available,occupied,reserved',
        ]);

        $table = Table::create($validated);

        return response()->json([
            'message' => 'Table created successfully',
            'data' => $table,
        ], 201);
    }

    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'table_number' => 'required|string|unique:tables,table_number,' . $table->id,
            'qr_code' => 'nullable|string',
            'status' => 'nullable|string|in:available,occupied,reserved',
        ]);

        $table->update($validated);

        return response()->json([
            'message' => 'Table updated successfully',
            'data' => $table,
        ]);
    }

    public function destroy($id)
    {
        $table = Table::findOrFail($id);
        $table->delete();

        return response()->json([
            'message' => 'Table deleted successfully',
        ]);
    }
}
