<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class TimeAfter implements ValidationRule
{
    protected $startTime;

    public function __construct($startTime)
    {
        $this->startTime = $startTime;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($this->startTime) || empty($value)) {
            return;
        }

        $start = Carbon::createFromFormat('H:i', $this->startTime);
        $end = Carbon::createFromFormat('H:i', $value);

        if ($end->lessThanOrEqualTo($start)) {
            $fail('Jam selesai harus setelah jam mulai.');
        }
    }
}