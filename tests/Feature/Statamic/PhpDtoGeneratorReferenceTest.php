<?php

use Ghijk\StatamicTypescriptGenerator\Mappers\FieldTypeMapper;
use Ghijk\StatamicTypescriptGenerator\Parsers\BlueprintParser;
use Ghijk\StatamicTypescriptGenerator\Parsers\FieldsetResolver;
use Ghijk\StatamicTypescriptGenerator\Generators\PhpDtoGenerator;
use Ghijk\StatamicTypescriptGenerator\Registry\SharedSetRegistry;

function makeReferenceGenerator(): PhpDtoGenerator
{
    $resolver = new FieldsetResolver;

    return new PhpDtoGenerator(
        new BlueprintParser($resolver),
        new FieldTypeMapper($resolver),
        $resolver,
    );
}

function resolvePhpTypeFor(PhpDtoGenerator $generator, array $field, array &$imports): array
{
    $method = new ReflectionMethod($generator, 'resolvePhpType');
    $method->setAccessible(true);
    $args = [$field, 'Fixture', &$imports, []];

    return $method->invokeArgs($generator, $args);
}

function sharedCodeFor(PhpDtoGenerator $generator): array
{
    $property = new ReflectionProperty($generator, 'sharedRegistry');
    $property->setAccessible(true);

    /** @var SharedSetRegistry $registry */
    $registry = $property->getValue($generator);

    return $registry->getGeneratedCode();
}

it('resolves single item reference fields to a shared reference DTO', function (string $type, array $config, string $class) {
    $generator = makeReferenceGenerator();
    $imports = [];

    $result = resolvePhpTypeFor($generator, ['handle' => 'field', 'type' => $type, 'config' => $config], $imports);

    expect($result)->toBe(['type' => $class, 'nullable' => true, 'docblock' => null])
        ->and($imports)->toContain("App\\StatamicDTO\\Shared\\{$class}")
        ->and(sharedCodeFor($generator))->toHaveKey($class);
})->with([
    'assets' => ['assets', ['max_files' => 1], 'AssetReference'],
    'entries' => ['entries', ['max_items' => 1], 'EntryReference'],
    'terms' => ['terms', ['max_items' => 1], 'TermReference'],
    'users' => ['users', ['max_items' => 1], 'UserReference'],
]);

it('resolves multi item reference fields to typed lists', function () {
    $generator = makeReferenceGenerator();
    $imports = [];

    $result = resolvePhpTypeFor($generator, ['handle' => 'topics', 'type' => 'terms', 'config' => []], $imports);

    expect($result)->toBe(['type' => 'array', 'nullable' => true, 'docblock' => '/** @var TermReference[]|null */']);

    $required = resolvePhpTypeFor($generator, ['handle' => 'topics', 'type' => 'terms', 'config' => [], 'required' => true], $imports);

    expect($required['docblock'])->toBe('/** @var TermReference[] */')
        ->and($required['nullable'])->toBeFalse();
});

it('renders the shared reference DTOs with the shallow augmentation shape', function () {
    $generator = makeReferenceGenerator();
    $imports = [];

    resolvePhpTypeFor($generator, ['handle' => 'logo', 'type' => 'assets', 'config' => ['max_files' => 1]], $imports);

    $code = sharedCodeFor($generator)['AssetReference'];

    expect($code)
        ->toContain('namespace App\\StatamicDTO\\Shared;')
        ->toContain('class AssetReference extends Data')
        ->toContain('public string $id;')
        ->toContain('public ?string $url = null;')
        ->toContain('public ?string $permalink = null;')
        ->toContain('public ?string $api_url = null;')
        ->toContain('public ?string $alt = null;')
        ->not->toContain('use App\\StatamicDTO\\Shared\\AssetReference;');
});

it('emits explicit literal TypeScript for loosely shaped fields', function (string $type, array $config, string $phpType, string $literal) {
    $generator = makeReferenceGenerator();
    $imports = [];

    $result = resolvePhpTypeFor($generator, ['handle' => 'field', 'type' => $type, 'config' => $config], $imports);

    expect($result['type'])->toBe($phpType)
        ->and($result['attributes'])->toBe(["#[LiteralTypeScriptType('{$literal}')]"])
        ->and($imports)->toContain('Spatie\\TypeScriptTransformer\\Attributes\\LiteralTypeScriptType');
})->with([
    'unknown fieldtype' => ['trigger_astro_build', [], 'mixed', 'unknown'],
    'belongs_to' => ['belongs_to', ['resource' => 'event_type'], 'array', 'Record<string, unknown> | null'],
    'has_many' => ['has_many', ['resource' => 'events'], 'array', 'Array<Record<string, unknown>> | null'],
    'table' => ['table', [], 'array', 'Array<Record<string, unknown>> | null'],
    'yaml' => ['yaml', [], 'array', 'Record<string, unknown> | null'],
    'replicator without sets' => ['replicator', [], 'array', 'Array<unknown> | null'],
]);

it('types width fields as integers', function () {
    $generator = makeReferenceGenerator();
    $imports = [];

    $result = resolvePhpTypeFor($generator, ['handle' => 'small_width', 'type' => 'width', 'config' => []], $imports);

    expect($result)->toBe(['type' => 'int', 'nullable' => true, 'docblock' => null]);
});
