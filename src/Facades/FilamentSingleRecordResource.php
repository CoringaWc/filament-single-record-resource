<?php

namespace CoringaWc\FilamentSingleRecordResource\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CoringaWc\FilamentSingleRecordResource\FilamentSingleRecordResource
 */
class FilamentSingleRecordResource extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CoringaWc\FilamentSingleRecordResource\FilamentSingleRecordResource::class;
    }
}
