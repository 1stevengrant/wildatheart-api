<?php

namespace App\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Transformers\Transformer;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContext;

class PassthroughCast implements Cast, Transformer
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        return $value;
    }

    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        return $value;
    }
}
