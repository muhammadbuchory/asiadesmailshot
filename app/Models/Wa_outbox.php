<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Mailcoach\Domain\Shared\Models\Concerns\UsesDatabaseConnection;
use Spatie\Mailcoach\Domain\Shared\Models\HasUuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wa_outbox extends Model
{
    use HasFactory;
    use HasUuid;
 
    use SoftDeletes;

    protected $table = 'wa_outbox';

}
