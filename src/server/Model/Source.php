<?php

namespace Nogo\Feedbox\Model;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    use \Eloquence\Database\Traits\CamelCaseModel;

    protected $fillable = ['user_id', 'name', 'slug', 'uri', 'enabled', 'period'];
    protected $guarded = ['id', 'last_update'];

    public function user()
    {
        return $this->belongsTo('Nogo\Feedbox\Model\User');
    }

}
