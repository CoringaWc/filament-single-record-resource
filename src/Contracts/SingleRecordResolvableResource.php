<?php

namespace CoringaWc\FilamentSingleRecordResource\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface SingleRecordResolvableResource
{
    public static function resolveSingleRecord(): ?Model;

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public static function resolveSingleRecordBuilder(Builder $query): Builder;
}
