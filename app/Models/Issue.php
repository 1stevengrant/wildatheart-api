<?php

namespace App\Models;

use App\Observers\IssueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(IssueObserver::class)]
class Issue extends BaseModel {}
