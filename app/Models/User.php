<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Traits\HasTwoFactorAuthentication;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as AuthMustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements AuthMustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, MustVerifyEmail, HasRoles, HasTwoFactorAuthentication;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'timezone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function address()
    {
        return $this->hasOne(UserAddress::class);
    }

    public function plantingEvents()
    {
        return $this->hasMany(PlantingEvent::class, 'planted_by', 'id');
    }

    public function maintenanceEvents()
    {
        return $this->hasMany(MaintenanceEvent::class, 'performed_by', 'id');
    }

    public function healthAssessments()
    {
        return $this->hasMany(HealthAssessment::class, 'assessed_by', 'id');
    }

    public function plantedEventTrees()
    {
        return $this->hasMany(PlantingEventTree::class, 'planted_by', 'id');
    }

    public function plantedTrees()
    {
        return $this->belongsToMany(Tree::class, 'planting_events_trees', 'planted_by', 'tree_id', 'id', 'id')->distinct();
    }

    protected $with = ['roles', 'address'];
}
