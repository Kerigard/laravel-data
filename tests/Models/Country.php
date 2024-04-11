<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Kerigard\LaravelData\Contracts\MustFillData;
use Kerigard\LaravelData\Data;

class Country extends Model implements MustFillData
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    public static function booted()
    {
        static::creating(function (Country $model) {
            $model->slug = Str::slug($model->name);
        });

        static::created(function (Country $model) {
            $model->slug = $model->slug.' Changed';
            $model->save();
        });
    }

    public function data(): Data
    {
        return Data::make([
            [
                'code' => 'USA',
                'name' => 'United States',
            ],
            [
                'code' => 'RUS',
                'name' => 'Russia',
            ],
        ], false)->withoutEvents(['eloquent.creating: '.Country::class]);
    }
}
