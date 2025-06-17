<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Page;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(Request $request, $pageId)
    {
        $data = $request->validate([
            'order' => 'nullable|integer',
            'background_color' => 'nullable|string',
            'background_image' => 'nullable|string',
            'type' => 'required|in:text,image,html,custom_component',
            'content' => 'required',
        ]);

        $data['page_id'] = $pageId;

        return Section::create($data);
    }

    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            'order' => 'nullable|integer',
            'background_color' => 'nullable|string',
            'background_image' => 'nullable|string',
            'type' => 'required|in:text,image,html,custom_component',
            'content' => 'required',
        ]);

        $section->update($data);

        return $section;
    }

    public function destroy(Section $section)
    {
        $section->delete();
        return response()->noContent();
    }

    public function reorder(Request $request, Page $page)
    {
        $request->validate([
            'order' => 'required|array',
        ]);

        foreach ($request->order as $index => $id) {
            Section::where('id', $id)
                ->where('page_id', $page->id)
                ->update(['order' => $index]);
        }

        return response()->json(['status' => 'success']);
    }
}
