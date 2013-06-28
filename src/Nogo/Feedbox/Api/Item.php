<?php
namespace Nogo\Feedbox\Api;

class Item extends AbstractApi
{
    /**
     * @var array
     */
    protected $fields = [
        'id' => [
            'read' => true,
            'write' => false
        ],
        'source_id' => [
            'read' => true,
            'write' => false
        ],
        'read' => [
            'read' => true,
            'write' => true
        ],
        'starred' => [
            'read' => true,
            'write' => true
        ],
        'title' => [
            'read' => true,
            'write' => false
        ],
        'pubdate' => [
            'read' => true,
            'write' => false
        ],
        'content' => [
            'read' => true,
            'write' => false
        ],
        'uid' => [
            'read' => true,
            'write' => false
        ],
        'uri' => [
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