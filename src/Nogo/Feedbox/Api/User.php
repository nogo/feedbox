<?php
namespace Nogo\Feedbox\Api;

class User extends AbstractApi
{
    /**
     * @var array
     */
    protected $fields = [];

    public function definition()
    {
        return $this->fields;
    }
}