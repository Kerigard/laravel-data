<?php

namespace Kerigard\LaravelData;

use Illuminate\Support\Collection;

class Data
{
    public Collection $rows;

    public array|bool $allowEvents = true;

    public function __construct(iterable $rows, public bool $delete = true)
    {
        $this->rows = collect($rows);
    }

    public static function make(iterable $rows, bool $delete = true): static
    {
        return new static($rows, $delete);
    }

    public function withoutEvents(array|string|bool $allow = false): self
    {
        $this->allowEvents = is_string($allow) ? [$allow] : $allow;

        return $this;
    }
}
