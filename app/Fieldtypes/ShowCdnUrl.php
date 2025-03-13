<?php

namespace App\Fieldtypes;

use Statamic\Fields\Fieldtype;
use App\Services\BunnyCDNService;

class ShowCdnUrl extends Fieldtype
{
    /**
     * The blank/default value.
     *
     * @return array
     */
    public function defaultValue()
    {
        return null;
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param  mixed  $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        $cdn = new BunnyCDNService;

        $asset = $this->field->parent();
        if ($asset) {
            return $cdn->getBunnyCDNUrl($asset->path());
        }

        return $data;
    }

    /**
     * Process the data before it gets saved.
     *
     * @param  mixed  $data
     * @return array|mixed
     */
    public function process($data)
    {
        return $data;
    }
}
