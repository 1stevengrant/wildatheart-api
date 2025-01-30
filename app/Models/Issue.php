<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(\App\Observers\IssueObserver::class)]
class Issue extends BaseModel {}
