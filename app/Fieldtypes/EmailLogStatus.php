<?php

namespace App\Fieldtypes;

use Statamic\Fields\Fieldtype;

class EmailLogStatus extends Fieldtype
{
    public $categories = ['special'];

    protected $selectable = false;
}
