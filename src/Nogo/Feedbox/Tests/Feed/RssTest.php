<?php

namespace Nogo\Feedbox\Tests\Feed;

use HTMLPurifier;
use Nogo\Feedbox\Feed\Rss;
use Nogo\Feedbox\Helper\HtmlPurifierSanitizer;

class RssTest extends \PHPUnit_Framework_TestCase
{

    public function testSanitize()
    {
        $rss = new Rss();
        $rss->setSanitizer(new HtmlPurifierSanitizer(new HTMLPurifier()));

        $content = file_get_contents(dirname(__FILE__) . '/amazon.rss');
        $rss->setContent($content);
        $result = $rss->execute();

        $this->assertNotEmpty($result);
        $this->assertNotEmpty($result[0]);
        $this->assertNotEmpty($result[0]['content']);
        $this->assertFalse(strstr($result[0]['title'], '<'));

        $content = file_get_contents(dirname(__FILE__) . '/xda.rss');
        $rss->setContent($content);
        $result = $rss->execute();

        $this->assertNotEmpty($result);
        $this->assertNotEmpty($result[2]);
        $this->assertNotEmpty($result[2]['content']);
        print_r($result[2]);
        $this->assertFalse(strstr($result[2]['title'], '<'));
        $this->assertTrue(strpos($result[2]['content'], '<iframe src="') !== false);
    }
}