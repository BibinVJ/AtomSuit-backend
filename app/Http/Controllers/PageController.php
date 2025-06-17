<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        return Page::all();
    }

    public function show($slug)
    {
        return Page::with('sections')->where('slug', $slug)->firstOrFail();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug' => 'required|unique:pages',
            'title' => 'required',
            'meta_title' => 'nullable',
            'meta_description' => 'nullable',
        ]);

        return Page::create($data);
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'slug' => 'required|unique:pages,slug,' . $page->id,
            'title' => 'required',
            'meta_title' => 'nullable',
            'meta_description' => 'nullable',
        ]);

        $page->update($data);

        return $page;
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return response()->noContent();
    }
}
