<?php

namespace App\Http\Controllers;

use App\Models\category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    //
    public function getCategoriesList(Request $request)
    {
        $search = $request->query('search');
        $data = Category::where('status', 'public')->latest()->get();

        if ($search) {
            $data = Category::where('status', 'public')
                ->where('name', 'like', "%{$search}%")
                ->latest()
                ->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $data
        ]);
    }
    public function index()
    {
        $data = Category::latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png,avif|max:5120',
            'status' => 'required|in:public,private'
        ]);

        $imageName = null;

        if ($request->hasFile('image')) {
            $localfile = Str::random(20) . '.' . $request->image->getClientOriginalExtension();

            $request->image->move(
                public_path('images'),
                $localfile
            );

            $imageName = '/images/' . $localfile;
        }

        $category = Category::create([
            'name' => $request->name,
            'image' => $imageName,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Data created successfully',
            'data' => $category,
        ], 201);
    }


    public function update($id, Request $request)
    {
        //
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,avif|max:5120',
            'status' => 'required|in:public,private'
        ]);

        $category = Category::findOrFail($id);

        $imageName = $category->image;

        if ($request->hasFile('image')) {
            $localfile = Str::random(20) . '.' . $request->image->getClientOriginalExtension();

            $request->image->move(
                public_path('images'),
                $localfile
            );

            $imageName = '/images/' . $localfile;
        }

        $category->update([
            'name' => $request->name,
            'image' => $imageName,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Data updated successfully',
            'data' => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Delete the image file if it exists
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data deleted successfully',
        ]);
    }
}
