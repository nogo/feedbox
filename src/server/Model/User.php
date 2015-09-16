<?php

namespace Nogo\Feedbox\Model;

class User extends Model
{
    use \Eloquence\Database\Traits\CamelCaseModel;

    protected $fillable = ['username', 'email', 'firstname', 'lastname', 'enabled'];
    protected $guarded = ['id', 'password', 'salt'];
    protected $hidden = ['password', 'salt'];
}
