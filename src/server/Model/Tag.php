<?php

namespace Nogo\Feedbox\Model;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use \Eloquence\Database\Traits\CamelCaseModel;

    protected $fillable = [ 'user_id', 'name', 'slug', 'public' ];
    protected $guarded = [ 'id' ];

    public function user()
    {
        return $this->belongsTo('Nogo\Feedbox\Model\User');
    }

}
