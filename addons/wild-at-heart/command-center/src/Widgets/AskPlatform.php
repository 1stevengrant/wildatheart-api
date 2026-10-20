<?php

namespace WildAtHeart\CommandCenter\Widgets;

use Statamic\Facades\User;
use Statamic\Widgets\Widget;
use Statamic\Widgets\VueComponent;

class AskPlatform extends Widget
{
    public function component(): ?VueComponent
    {
        if (! User::current()?->isSuper()) {
            return null;
        }

        return VueComponent::render('ask-platform-widget', [
            'url' => cp_route('command-center.ask.index'),
        ]);
    }
}
