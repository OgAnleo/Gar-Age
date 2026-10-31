<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * One booked day of a contract in the "specific days" mode.
 *
 * @property int $id
 * @property int $contract_id
 * @property string $day
 */
#[Fillable(['day'])]
class ContractDay extends Model
{
    public $timestamps = false;
}
