<?php
namespace Nogo\Feedbox\Api;


class Tag extends AbstractApi
{

    /**
     * @var array
     */
    protected $fields = [
        'id' => [
            'read' => true,
            'write' => false
        ],
        'name' => [
            'read' => true,
            'write' => true
        ],
        'color' => [
            'read' => true,
            'write' => true
        ],
        'sources' => [
            'read' => true,
            'write' => false
        ],
        'unread' => [
            'read' => true,
            'write' => false
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