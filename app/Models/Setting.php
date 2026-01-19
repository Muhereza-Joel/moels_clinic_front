<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, HasUuid, BelongsToOrganization;

    protected $table = 'settings';

    protected $fillable = [
        'uuid',
        'organization_id',
        'key',
        'value',
        'category',
        'is_active',
    ];

    protected $casts = [
        'value' => 'array',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Helpers
    public static function getValue($organizationId, $key, $default = null)
    {
        $setting = self::where('organization_id', $organizationId)
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        return $setting ? $setting->value : $default;
    }

    public static function setValue($organizationId, $key, $value, $category = null)
    {
        return self::updateOrCreate(
            ['organization_id' => $organizationId, 'key' => $key],
            ['value' => $value, 'category' => $category]
        );
    }
}
