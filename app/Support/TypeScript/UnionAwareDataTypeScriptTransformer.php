<?php

namespace App\Support\TypeScript;

use ReflectionProperty;
use phpDocumentor\Reflection\Type;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptTransformer;

/**
 * Laravel Data resolves an iterable property down to a single `dataClass`, so a
 * docblock such as `array<HeroSet|ButtonsSet>` transforms to `Array<HeroSet>`
 * and every other member of the union is silently dropped.
 *
 * Whenever the docblock does declare a union, this transformer falls back to
 * the reflection based type resolution, which keeps all of the members.
 */
class UnionAwareDataTypeScriptTransformer extends DataTypeScriptTransformer
{
    protected function resolveTypeForProperty(
        ReflectionProperty $property,
        DataProperty $dataProperty,
        MissingSymbolsCollection $missingSymbols,
    ): ?Type {
        if ($this->declaresUnionOfItemTypes($property)) {
            return $this->reflectionToType(
                $property,
                $missingSymbols,
                ...$this->typeProcessors()
            );
        }

        return parent::resolveTypeForProperty($property, $dataProperty, $missingSymbols);
    }

    private function declaresUnionOfItemTypes(ReflectionProperty $property): bool
    {
        $docComment = $property->getDocComment();

        if ($docComment === false) {
            return false;
        }

        if (! preg_match('/@var\s+(?:array|list|iterable)\s*<([^>]+)>/i', $docComment, $matches)) {
            return false;
        }

        $segments = explode(',', $matches[1]);

        return str_contains(mb_trim(end($segments)), '|');
    }
}
