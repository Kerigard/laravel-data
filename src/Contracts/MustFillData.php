<?php

namespace Kerigard\LaravelData\Contracts;

use Kerigard\LaravelData\Data;

interface MustFillData
{
    public function data(): Data;
}
