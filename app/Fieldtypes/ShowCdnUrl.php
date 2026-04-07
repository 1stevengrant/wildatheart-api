<?php

namespace App\Fieldtypes;

use Statamic\Fields\Fieldtype;
use App\Services\BunnyCDNService;

class ShowCdnUrl extends Fieldtype
{
    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param  mixed  $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        $cdn = app(BunnyCDNService::class);

        $asset = $this->field->parent();
        if ($asset) {
            return $cdn->getBunnyCDNUrl($asset->path());
        }

        return $data;
    }
}
