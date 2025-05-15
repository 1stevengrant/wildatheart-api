<?php

namespace App\Models;

class IpLookup extends BaseModel
{
    protected function casts(): array
    {
        return [
            'raw_data' => 'array',
            'last_lookup_at' => 'datetime',
        ];

    }
}
