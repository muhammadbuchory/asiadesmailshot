<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Mailcoach\Domain\Shared\Traits\UsesMailcoachModels;
use Spatie\Mailcoach\Domain\Shared\Models\Concerns\UsesDatabaseConnection;
use Spatie\Mailcoach\Domain\Shared\Models\HasUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Mailcoach\Domain\Campaign\Enums\CampaignStatus;
use Spatie\Mailcoach\Domain\Shared\Models\Concerns\SendsToSegment;

class Wa_campaigns extends Model
{
    use HasFactory;
    use HasUuid;
    use UsesDatabaseConnection;
    use UsesMailcoachModels;
    use SendsToSegment;

    use SoftDeletes;

    protected $table = 'wa_campaigns';

    public $casts = [
        'status' => CampaignStatus::class,
        'schedule_at' => 'datetime',
    ];

}
