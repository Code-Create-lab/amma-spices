<?php

namespace App\Http\Controllers\Admin\Attribute;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Attribute\AttributeStoreRequest;
use App\Http\Requests\Admin\Attribute\AttributeUpdateRequest;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::where('is_deleted', 0)->paginate(10);
        return view('admin.attributes.index', [
            'attributes' => $attributes
        ]);
    }
    public function create()
    {
        return view('admin.attributes.create');
    }
    public function store(AttributeStoreRequest $request)
    {
        $data = [
            'name' => $request->name
        ];
        $attribute_option_data = json_decode($request['options'][0], true);
        $optionRecords = array_map(fn($option) => ['name' => $option], $attribute_option_data);
        $attribute = Attribute::create($data);
        $attribute->values()->createMany($optionRecords);
        return Redirect::route('attributes')->with('success', 'Attribute Created Successfully!');
    }
    public function edit($uuid)
    {
        $attribute = Attribute::with(['values'])->where('uuid', $uuid)->firstOrFail();
        return view('admin.attributes.edit', [
            'attribute' => $attribute
        ]);
    }
    public function update(AttributeUpdateRequest $request, $uuid)
    {
        $attribute = Attribute::with('values')->where('uuid', $uuid)->firstOrFail();
        // Update the attribute name
        $attribute->update([
            'name' => $request->name,
            // 'value' => $request->value
        ]);
        $newOptions = json_decode($request->options[0], true) ?? []; // Ensure it's always an array
        $existingOptions = $attribute->values->pluck('name')->toArray() ?? [];
        $optionsToAdd = array_diff($newOptions, $existingOptions);
        $optionsToDelete = array_diff($existingOptions, $newOptions);
        foreach ($attribute->values as $option) {
            if (in_array($option->name, $optionsToDelete)) {
                $option->update(['is_deleted' => 1]);
            }
        }
        foreach ($optionsToAdd as $option) {
            $attributeValue = $attribute->values()->create(['name' => $option, 'value' => $option]);
            $attributeValue->value = $option;
            $attributeValue->save();
        }
        return Redirect::route('attributes')->with('success', 'Attribute Updated Successfully!');
    }


    public function delete($uuid)
    {
        $attribute = Attribute::where('uuid', $uuid)->firstOrFail();
        $attribute->update([
            'is_deleted' => 1
        ]);
        return Redirect::route('attributes')->with('success', 'Attribute Deleted Successfully!');
    }
}
