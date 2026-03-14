<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory;
    protected $primaryKey = 'cat_id';


    protected $fillable = [
        'title',
        'slug',
        'image',
        'parent',
        'level',
        'description',
        'status',
        'is_deleted'
    ];


    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('is_deleted', 0);
        });
    }


    public function sub_categories()
    {
        return $this->hasMany(Category::class, 'parent', 'cat_id');
    }
    public function sub_category_parent()
    {
        return $this->belongsTo(Category::class, 'parent', 'cat_id');
    }

    public function sub_cat_product()
    {

        return $this->hasMany(Product::class, 'cat_id', 'cat_id')->where('is_deleted', 0);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'cat_id', 'cat_id')->where('is_deleted', 0);
    }

    public function getSubCategoryProductStats()
    {

        // Eager load sub_categories to avoid N+1 queries
        $this->load('sub_categories');

        // dd($this->sub_categories->map(function ($subCat) {
        //     return [
        //         'parent_id' => $this->cat_id,
        //         'sub_category_id' => $subCat->cat_id,
        //         'products' => $subCat->sub_cat_product()->count(), // returns a collection or count
        //     ];
        // })->toArray());


        return $this->sub_categories->map(function ($subCat) {
            return [
                'parent_id' => $this->cat_id,
                'sub_category_id' => $subCat->cat_id,
                'products' => $subCat->sub_cat_product()->count(), // returns a collection or count
            ];
        })->toArray();
        //   return $this->hasMany(Product::class);
    }


    public function fullRouteParams()
    {
        $params = [];

        // Level 3 → Child (has parent and grandparent)
        if ($this->parentObj && $this->parentObj->parentObj) {
            $params[] = $this->parentObj->parentObj->slug; // main
            $params[] = $this->parentObj->slug;            // sub
            $params[] = $this->slug;                       // child

            return $params;
        }

        // Level 2 → Sub category
        if ($this->parentObj) {
            $params[] = $this->parentObj->slug; // main
            $params[] = $this->slug;            // sub

            return $params;
        }

        // Level 1 → Main category
        return [$this->slug];
    }

    public function parentObj()
    {
        return $this->belongsTo(Category::class, 'parent', 'cat_id');
    }
}
