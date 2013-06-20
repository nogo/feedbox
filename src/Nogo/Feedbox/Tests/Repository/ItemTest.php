<?php
namespace Nogo\Feedbox\Tests\Repository;

use Nogo\Feedbox\Helper\DatabaseConnector;
use Nogo\Feedbox\Repository\Item;

class ItemTest extends \PHPUnit_Framework_TestCase
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
        $item = new Item($this->db->getInstance());
        $result = $item->validate(array(
            'id' => 1,
            'read' => date('Y-m-d H:i:s'),
            'starred' => '1',
            'test' => 'xyz'
        ));

        $this->assertArrayHasKey('read', $result);
        $this->assertArrayHasKey('starred', $result);
        $this->assertArrayNotHasKey('test', $result);
        $this->assertEquals(true, $result['starred']);
    }
}