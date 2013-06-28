<?php
namespace Nogo\Feedbox\Api;

class Source extends AbstractApi
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
        'uri' => [
            'read' => true,
            'write' => true
        ],
        'icon' => [
            'read' => true,
            'write' => false
        ],
        'active' => [
            'read' => true,
            'write' => true
        ],
        'unread' => [
            'read' => true,
            'write' => false
        ],
        'errors' => [
            'read' => true,
            'write' => false
        ],
        'period' => [
            'read' => true,
            'write' => true
        ],
        'last_update' => [
            'read' => true,
            'write' => true
        ],
        'tag_id' => [
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