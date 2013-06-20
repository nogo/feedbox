<?php
namespace Nogo\Feedbox\Tests\Repository;

use Nogo\Feedbox\Helper\DatabaseConnector;
use Nogo\Feedbox\Repository\Source;

class SourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DatabaseConnector
     */
    protected $db;

    public function setUp()
    {
        $this->db = new DatabaseConnector('sqlite', ':memory', '', '');
    }

    public function testValidate()
    {
        $source = new Source($this->db->getInstance());
        $result = $source->validate(array(
            'id' => 1,
            'name' => "Adam Bien<script>alert('test')</script>",
            'period' => 'daily',
            'created_at' => date('Y-m-d H:i:s'),
            'test' => 'xyz'
        ));

        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('period', $result);
        $this->assertArrayNotHasKey('test', $result);
        $this->assertEquals('daily', $result['period']);
        $this->assertEquals('Adam Bien', $result['name']);
    }
}