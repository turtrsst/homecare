<?php

namespace App\Enums;

enum TimeWindow: string
{
    case Morning = 'morning';
    case Midday = 'midday';
    case Afternoon = 'afternoon';

    public function label(): string
    {
        return config("homecare.time_windows.{$this->value}.label", $this->name);
    }

    public function start(): string
    {
        return config("homecare.time_windows.{$this->value}.start", '08:00');
    }

    public function end(): string
    {
        return config("homecare.time_windows.{$this->value}.end", '12:00');
    }
}
