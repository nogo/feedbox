<?php

namespace Nogo\Feedbox\Model;

use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    protected $table = 'access';

    protected $fillable = [ 'user_id', 'client' ];
    protected $guarded = [ 'token' ];
    protected $hidden = [ 'token' ];

    public function user()
    {
        return $this->belongsTo('Nogo\Feedbox\Model\User');
    }

    /**
     * Compare updated_at with strtotime('-' . $period).
     *
     * @param string $period will be strtotime
     * @return boolean
     */
    public function expired($period)
    {
        return strtotime('-' . $period) >= strtotime($this->updated_at);
    }

    /**
     * Return expire date
     * @param string $period
     * @return string
     */
    public function expires($period)
    {
        return date('Y-m-d H:i:s', strtotime($period, strtotime($this->updated_at)));
    }
}
