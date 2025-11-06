<?php

namespace App\Entities;

use App\Models\Navlink;
use Illuminate\Support\Collection;

class Navbar
{

    public function getNavbar(): Collection
    {
        return Navlink::where('enabled', true)->whereNull('parent_id')->orderBy('id', 'asc')->get();
    }

}