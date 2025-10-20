<?php

namespace App\Entities;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Message
{
    public function __construct(private Request $request)
    {
    }

    public function getMessage(): ?array
    {
        $msg = $this->request->session()->get('message');
        if (!$msg) {
            return null;
        }

        return array_merge($msg, ['fingerprint' => Str::uuid()]);
    }
}