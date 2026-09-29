<?php

namespace App\Data\V1;

use Illuminate\Support\Str;
use Spatie\LaravelData\Data;
use Statamic\Facades\GlobalSet;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class HostAgreementData extends Data
{
    public function __construct(
        public string $content_html,
    ) {}

    public static function fromGlobals(): self
    {
        $content = GlobalSet::findByHandle('local_platform')
            ?->in('default')
            ?->get('host_agreement_content');

        return new self(
            content_html: Str::markdown($content ?? ''),
        );
    }
}
