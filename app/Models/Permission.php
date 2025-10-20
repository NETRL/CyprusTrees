<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin IdeHelperPermission
 */
class Permission extends SpatiePermission
{
    use HasFactory;

    protected $with = ['children'];

    protected $appends = ['type'];

    /**
     * Get the children for this permission.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Permission::class, 'parent_id');
    }

    public function getTypeAttribute(): string
    {
        return str_contains($this->name, '.') ? explode('.', $this->name)[1] : '';
    }

    public function makeCRUDPermissions($request)
    {
        Permission::create([
            'name'        => $request->input('name') . '.view',
            'group_name'  => $request->input('name'),
            'description' => 'Can view ' . $request->input('name') . '.',
            'parent_id'   => $this->id
        ]);
        Permission::create([
            'name'        => $request->input('name') . '.edit',
            'group_name'  => $request->input('name'),
            'description' => 'Can edit existing ' . $request->input('name') . '.',
            'parent_id'   => $this->id
        ]);
        Permission::create([
            'name'        => $request->input('name') . '.create',
            'group_name'  => $request->input('name'),
            'description' => 'Can create new ' . $request->input('name') . '.',
            'parent_id'   => $this->id
        ]);
        Permission::create([
            'name'        => $request->input('name') . '.delete',
            'group_name'  => $request->input('name'),
            'description' => 'Can delete ' . $request->input('name') . '.',
            'parent_id'   => $this->id
        ]);
    }
}
