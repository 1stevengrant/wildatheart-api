<?php

use App\Data\ProfileMenuData;
use App\Data\ProfileMenuItemData;

it('creates a profile menu item with all fields', function () {
    $item = new ProfileMenuItemData(
        label: 'My Giving',
        href: 'https://example.com/giving',
        iconName: 'heart',
    );

    expect($item->label)->toBe('My Giving')
        ->and($item->href)->toBe('https://example.com/giving')
        ->and($item->iconName)->toBe('heart');
});

it('creates a profile menu item with nullable icon', function () {
    $item = new ProfileMenuItemData(
        label: 'Host an Event',
        href: 'https://example.com/host',
    );

    expect($item->label)->toBe('Host an Event')
        ->and($item->href)->toBe('https://example.com/host')
        ->and($item->iconName)->toBeNull();
});

it('creates a profile menu data with items', function () {
    $menu = new ProfileMenuData(
        urlToProfile: 'https://example.com/profile',
        primaryItems: [
            new ProfileMenuItemData(label: 'My Giving', href: '/giving', iconName: 'heart'),
            new ProfileMenuItemData(label: 'My Events', href: '/events', iconName: 'bookSquare'),
        ],
        secondaryItems: [
            new ProfileMenuItemData(label: 'Host an Event', href: '/host'),
        ],
    );

    expect($menu->urlToProfile)->toBe('https://example.com/profile')
        ->and($menu->primaryItems)->toHaveCount(2)
        ->and($menu->secondaryItems)->toHaveCount(1)
        ->and($menu->primaryItems[0]->label)->toBe('My Giving')
        ->and($menu->primaryItems[1]->label)->toBe('My Events')
        ->and($menu->secondaryItems[0]->label)->toBe('Host an Event');
});

it('creates an empty profile menu data', function () {
    $menu = new ProfileMenuData(
        urlToProfile: 'https://example.com/profile',
        primaryItems: [],
        secondaryItems: [],
    );

    expect($menu->urlToProfile)->toBe('https://example.com/profile')
        ->and($menu->primaryItems)->toBeEmpty()
        ->and($menu->secondaryItems)->toBeEmpty();
});
