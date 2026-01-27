<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;

class PdfTemplate extends Model
{
    use SoftDeletes, HasUuid, BelongsToOrganization;

    protected $fillable = [
        'uuid',
        'organization_id',
        'code',
        'name',
        'layout',
        'version',
        'active',
    ];

    protected $casts = [
        'layout' => 'array',
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /** * Load template layout, falling back to default JSON file. */ public function getResolvedLayout(): array
    {
        if ($this->layout) {
            return $this->layout;
        }
        $path = resource_path("templates/pdf/defaults/{$this->code}.json");
        if (File::exists($path)) {
            return json_decode(File::get($path), true);
        }
        return [];
    }
}
