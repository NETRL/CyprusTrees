<?php

namespace App\Entities;

use App\Models\User;
use Illuminate\Http\Request;

use function Illuminate\Log\log;

class Auth
{
    private ?User $user;

    public function __construct(Request $request)
    {
        $this->user = $request->user();
    }

    /**
     * @return bool
     */
    private function hasUnreadNotifications(): bool
    {
        return $this->user->unreadNotifications()->exists();
    }

    /**
     * @return null
     */
    private function getUserPermissions()
    {
        return $this->user->getAllPermissions();
    }

    public function toArray(): array
    {
        return $this->user ? [
            'user'                   => $this->user,
            'permissions'            => $this->getUserPermissions(),
            // 'hasUnreadNotifications' => $this->hasUnreadNotifications()
        ] : [];
    }


}