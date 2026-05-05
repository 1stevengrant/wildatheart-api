<?php

use Ghijk\StatamicTypescriptGenerator\Mappers\FieldTypeMapper;
use Ghijk\StatamicTypescriptGenerator\Parsers\BlueprintParser;
use Ghijk\StatamicTypescriptGenerator\Parsers\FieldsetResolver;
use Ghijk\StatamicTypescriptGenerator\Generators\PhpDtoGenerator;
use Ghijk\StatamicTypescriptGenerator\Registry\SharedSetRegistry;

function makeGenerator(): PhpDtoGenerator
{
    $resolver = new FieldsetResolver;

    return new PhpDtoGenerator(
        new BlueprintParser($resolver),
        new FieldTypeMapper($resolver),
        $resolver,
    );
}

function invokeResolveLinkType(PhpDtoGenerator $generator, bool $required, array &$imports): array
{
    $method = new ReflectionMethod($generator, 'resolveLinkType');
    $method->setAccessible(true);
    $args = [$required, &$imports];

    return $method->invokeArgs($generator, $args);
}

function getSharedRegistry(PhpDtoGenerator $generator): SharedSetRegistry
{
    $property = new ReflectionProperty($generator, 'sharedRegistry');
    $property->setAccessible(true);

    return $property->getValue($generator);
}

it('resolves a non-required link field as a nullable LinkField with shared import', function () {
    $generator = makeGenerator();
    $imports = [];

    $result = invokeResolveLinkType($generator, false, $imports);

    expect($result)->toBe([
        'type' => 'LinkField',
        'nullable' => true,
        'docblock' => null,
    ]);
    expect($imports)->toContain('App\\StatamicDTO\\Shared\\LinkField');
});

it('marks a required link field as non-nullable', function () {
    $generator = makeGenerator();
    $imports = [];

    $result = invokeResolveLinkType($generator, true, $imports);

    expect($result['nullable'])->toBeFalse();
    expect($result['type'])->toBe('LinkField');
});

it('registers the shared LinkField DTO with url, text, and newWindow properties', function () {
    $generator = makeGenerator();
    $imports = [];

    invokeResolveLinkType($generator, false, $imports);

    $code = getSharedRegistry($generator)->getGeneratedCode();

    expect($code)->toHaveKey('LinkField');

    $linkFieldCode = $code['LinkField'];

    expect($linkFieldCode)
        ->toContain('namespace App\\StatamicDTO\\Shared;')
        ->toContain('class LinkField extends Data')
        ->toContain('public ?string $url')
        ->toContain('public ?string $text')
        ->toContain('public bool $newWindow = false');
});

it('only generates the shared LinkField DTO once across multiple link fields', function () {
    $generator = makeGenerator();
    $imports = [];

    invokeResolveLinkType($generator, false, $imports);
    invokeResolveLinkType($generator, true, $imports);
    invokeResolveLinkType($generator, false, $imports);

    $code = getSharedRegistry($generator)->getGeneratedCode();

    expect(array_keys($code))->toBe(['LinkField']);
});
