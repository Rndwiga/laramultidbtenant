<?php

namespace rndwiga\MultiTenant;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'laramultitenantdb';
    }
} 