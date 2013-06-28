<?php
namespace Nogo\Feedbox\Api;

class Setting extends AbstractApi
{
    /**
     * @var array
     */
    protected $fields = [
        'id' => [
            'read' => true,
            'write' => false
        ],
        'key' => [
            'read' => true,
            'write' => true
        ],
        'value' => [
            'read' => true,
            'write' => true
        ],
        'created_at' => [
            'read' => true,
            'write' => false
        ],
        'updated_at' => [
            'read' => true,
            'write' => false
        ]
    ];

    public function definition()
    {
        return $this->fields;
    }
}