<?php

namespace App\Fieldtypes;

use Statamic\Fields\Fieldtype;

class EmailHtmlPreview extends Fieldtype
{
    public $categories = ['special'];

    protected $selectable = false;
}
