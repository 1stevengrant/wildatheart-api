<?php

use Statamic\Facades\Fieldset;
use App\StatamicDTO\Shared\HeroSet;

/**
 * @return list<string>
 */
function fieldsetBardSetHandles(string $fieldsetHandle): array
{
    $fieldset = Fieldset::find($fieldsetHandle);

    expect($fieldset)->not->toBeNull();

    $blocksField = collect($fieldset->contents()['fields'])
        ->firstWhere('handle', 'blocks');

    return collect($blocksField['field']['sets'] ?? [])
        ->flatMap(fn (array $group): array => array_keys($group['sets'] ?? []))
        ->values()
        ->all();
}

it('makes code blocks available in the hero nested block builder', function () {
    expect(fieldsetBardSetHandles('hero'))->toContain('code_block');
});

it('makes code blocks available in the main block builder', function () {
    expect(fieldsetBardSetHandles('blocks'))->toContain('code_block');
});

it('includes code blocks in the generated Hero DTO', function () {
    $docblock = (new ReflectionProperty(HeroSet::class, 'blocks'))->getDocComment();

    expect($docblock)->toContain('CodeBlockSet');
});
