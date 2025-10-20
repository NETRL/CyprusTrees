<?php

namespace App\Entities;

use App\Models\Navlink;
use Illuminate\Support\Collection;

class Navbar
{

    public function getNavbar(): Collection
    {
        return Navlink::whereNull('parent_id')->get();
    }

}