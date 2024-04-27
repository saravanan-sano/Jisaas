<?php

namespace App\Models;


use App\Classes\Common;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;

class HelpVideo extends BaseModel
{
    protected $table = 'help_videos';

    protected $default = ['id', 'xid', 'name'];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $appends = ['xid'];

    protected $hidden = ['id'];

    protected $filterable = ['id', 'pagename'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

}
