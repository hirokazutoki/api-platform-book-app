<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
class Tag
{
    public function __construct(
        public int $id,
        public string $label,
    ) {
        //
    }
}
