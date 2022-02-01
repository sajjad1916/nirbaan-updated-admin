<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\ModelStatus\Status;
use DateTimeInterface;


class OrderStatus extends Status
{
    protected $guarded = [];

    protected $table = 'statuses';


    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}
