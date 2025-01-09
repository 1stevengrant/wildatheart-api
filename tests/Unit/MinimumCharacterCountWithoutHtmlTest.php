<?php

use App\Rules\MinimumCharacterCountWithoutHtml;

beforeEach(function () {
    $this->rule = new MinimumCharacterCountWithoutHtml(10);
});
it('passes validation when clean text length meets minimum character requirement', function () {

    $validate = fn (string $value) => $this->rule->validate('field', $value,
        fn ($message) => throw new Exception($message)
    );

    expect(fn () => $validate('1234567890'))->not()->toThrow(Exception::class)
        ->and(fn () => $validate('12345678901'))->not()->toThrow(Exception::class);
});

it('fails validation when clean text length is below minimum requirement', function () {

    $validate = fn (string $value) => $this->rule->validate('field', $value,
        fn ($message) => throw new Exception($message)
    );

    expect(fn () => $validate('123456789'))
        ->toThrow(Exception::class, 'The :attribute must be at least 10 characters.');
});

it('correctly strips HTML tags before counting characters', function () {

    $validate = fn (string $value) => $this->rule->validate('field', $value,
        fn ($message) => throw new Exception($message)
    );

    expect(fn () => $validate('<p>1234567890</p>'))->not()->toThrow(Exception::class)
        ->and(fn () => $validate('<p>123456789</p>'))
        ->toThrow(Exception::class, 'The :attribute must be at least 10 characters.');

});

it('accepts custom minimum character counts', function () {
    $rule = new MinimumCharacterCountWithoutHtml(15);

    $validate = fn (string $value) => $rule->validate('field', $value,
        fn ($message) => throw new Exception($message)
    );

    expect(fn () => $validate('123456789012345'))->not()->toThrow(Exception::class)
        ->and(fn () => $validate('12345678901234'))
        ->toThrow(Exception::class, 'The :attribute must be at least 15 characters.');
});

it('handles complex HTML structures correctly', function () {

    $validate = fn (string $value) => $this->rule->validate('field', $value,
        fn ($message) => throw new Exception($message)
    );

    $complexHtml = '
        <div class="content">
            <p>Hello <strong>World</strong></p>
            <ul>
                <li>Test</li>
            </ul>
        </div>
    ';

    expect(fn () => $validate($complexHtml))->not()->toThrow(Exception::class);
});

it('handles empty HTML tags correctly', function () {

    $validate = fn (string $value) => $this->rule->validate('field', $value,
        fn ($message) => throw new Exception($message)
    );

    expect(fn () => $validate('<p></p><div></div>'))
        ->toThrow(Exception::class, 'The :attribute must be at least 10 characters.');
});
