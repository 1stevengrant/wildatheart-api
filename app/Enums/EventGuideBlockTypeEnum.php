<?php

namespace App\Enums;

enum EventGuideBlockTypeEnum: string
{
    case BodySet = 'body_set';
    case TextSet = 'text_set';
    case VideoSet = 'video_set';
    case ImageSet = 'image_set';
    case AccordionSet = 'accordion_set';
}
