<?php

namespace App\Http\Controllers;

use App\Models\food;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FoodController extends Controller
{
    //
    public function index()
    {
        $foods = food::with('category')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'status' => true,
            'data' => $foods
        ]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:10240',
            'status' => 'required|in:active,inactive',
        ]);

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $localfile = Str::random(20) . '.' . $request->image->getClientOriginalExtension();

             $request->image->move(
                public_path('food_images'),
                $localfile
            );
            $validatedData['image'] = '/food_images/' . $localfile;
        }

        // Create the food item in the database
        $food = food::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Food item created successfully.',
            'data' => $food->load('category'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        // Find the food item by ID
        $food = food::findOrFail($id);

        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:10240',
            'status' => 'required|in:active,inactive',
        ]);

         $validatedData['image'] = $food->image; // Keep the existing image path if no new image is uploaded

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $localfile = Str::random(20) . '.' . $request->image->getClientOriginalExtension();

             $request->image->move(
                public_path('food_images'),
                $localfile
            );
            $validatedData['image'] = '/food_images/' . $localfile;
        }

        // Update the food item in the database
        $food->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Food item updated successfully.',
            'data' => $food->load('category'),
        ]);
    }

    public function destroy($id)
    {
        // Find the food item by ID
        $food = food::findOrFail($id);

        // delete the image file if it exists
        if ($food->image && file_exists(public_path($food->image))) {
            unlink(public_path($food->image));
        }

        // Delete the food item from the database
        $food->delete();

        return response()->json([
            'status' => true,
            'message' => 'Food item deleted successfully.',
        ]);
    }

    public function getFoodCategoriesList(Request $request)
    {
        $categoryId = $request->query('category_id');

         $foods = food::query();
        // Validate the category_id parameter
        if ($categoryId && is_numeric($categoryId)) {
            $foods = $foods->where('category_id', $categoryId);
        }

        // Fetch foods based on the provided category_id
        $foods = $foods->get();

        return response()->json([
            'status' => true,
            'data' => $foods->load('category'),
        ]);
    }
}
