<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DrugCategory extends Model
{
    use HasFactory, HasUuid, SoftDeletes, BelongsToOrganization;

    protected $table = 'drug_categories';

    protected $fillable = [
        'uuid',
        'organization_id',
        'name',
        'code',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'full_name',
        'hierarchical_name',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // Relationships
    public function parent()
    {
        return $this->belongsTo(DrugCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DrugCategory::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function drugs()
    {
        return $this->hasMany(Drug::class, 'category_id');
    }

    public function subcategoryDrugs()
    {
        return $this->hasMany(Drug::class, 'subcategory_id');
    }

    // Scopes
    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->name . ($this->code ? " ({$this->code})" : '');
    }

    public function getHierarchicalNameAttribute(): string
    {
        $names = [];
        $category = $this;

        while ($category) {
            $names[] = $category->name;
            $category = $category->parent;
        }

        return implode(' > ', array_reverse($names));
    }

    public function getLevelAttribute(): int
    {
        $level = 0;
        $category = $this;

        while ($category->parent) {
            $level++;
            $category = $category->parent;
        }

        return $level;
    }

    public function getAllChildrenIdsAttribute(): array
    {
        $ids = [$this->id];

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->allChildrenIds);
        }

        return $ids;
    }

    // Business Logic
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function hasDrugs(): bool
    {
        return $this->drugs()->exists() || $this->subcategoryDrugs()->exists();
    }

    public function canBeDeleted(): bool
    {
        return !$this->hasChildren() && !$this->hasDrugs();
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $category = $this;

        while ($category) {
            $breadcrumbs[] = [
                'id' => $category->id,
                'name' => $category->name,
            ];
            $category = $category->parent;
        }

        return array_reverse($breadcrumbs);
    }

    public function getDrugCount(): int
    {
        return $this->drugs()->count() + $this->subcategoryDrugs()->count();
    }

    // Static Methods
    public static function getHierarchicalList($organizationId = null)
    {
        $query = self::with('children.children')->orderBy('sort_order')->orderBy('name');

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        return $query->get();
    }

    public static function getFlatList($organizationId = null, $includeCode = true)
    {
        $query = self::with('parent')->orderBy('sort_order')->orderBy('name');

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $categories = $query->get();
        $flatList = [];

        foreach ($categories as $category) {
            $prefix = str_repeat('--', $category->level);
            $name = $prefix . ($prefix ? ' ' : '') . $category->name;
            if ($includeCode && $category->code) {
                $name .= " ({$category->code})";
            }
            $flatList[$category->id] = $name;
        }

        return $flatList;
    }
}
