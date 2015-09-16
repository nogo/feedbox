<?php

namespace Nogo\Feedbox\Model;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use \Eloquence\Database\Traits\CamelCaseModel;

    protected $fillable = [ 'user_id', 'source_id', 'uid', 'uri', 'title', 'content' ];
    protected $guarded = [ 'id' ];

    public function user()
    {
        return $this->belongsTo('Nogo\Feedbox\Model\User');
    }

    public function tags()
    {
        return $this->belongsToMany('Nogo\Feedbox\Model\Tag', 'item_tags');
    }

}
