<?php

namespace ThemisMin\AdminConfig\Traits\Models;

use App\Models\SiteSeo;

trait HasSiteSeo
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function siteSeo()
    {
        return $this->morphOne(SiteSeo::class, 'model');
    }
}
