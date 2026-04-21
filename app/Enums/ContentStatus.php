<?php

namespace App\Enums;

enum ContentStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
}
