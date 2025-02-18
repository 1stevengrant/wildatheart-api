<?php

namespace Ghijk\WildAtHeart\Http\Controllers\CP;

use Statamic\Http\Controllers\CP\CpController;

class WildAtHeartController extends CpController
{
    public function index()
    {
        return view('wild-at-heart::index');
    }
}
