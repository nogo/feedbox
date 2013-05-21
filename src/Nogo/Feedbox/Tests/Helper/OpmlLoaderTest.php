<?php

use Nogo\Feedbox\Helper\OpmlLoader;

class OpmlLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $mockContent;


    public function setUp()
    {
        $this->mockContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<opml version="1.0">
    <head>
        <title>Abonnements von nogo in Google Reader</title>
    </head>
    <body>
        <outline text="Amazon.de: Top 100 Kostenlos"
            title="Amazon.de: Top 100 Kostenlos" type="rss"
            xmlUrl="http://www.amazon.de/gp/rss/top-free/digital-text/530886031/ref=zg_tf_530886031_rsslink" htmlUrl="http://www.amazon.de/gp/bestsellers/digital-text/530886031/ref=pd_zg_rss_tf_kinc_530886031_c"/>
    </body>
</opml>
XML;

    }

    public function testSetContent()
    {
        $opml = new OpmlLoader();

        $opml->setContent($this->mockContent);

        $this->assertNotNull($opml->getXml());
        $this->assertInstanceOf('SimpleXMLElement', $opml->getXml());
    }

    public function testRun()
    {
        $opml = new OpmlLoader();
        $opml->setContent($this->mockContent);

        $result = $opml->run();
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertEquals('Amazon.de: Top 100 Kostenlos', $result[0]['name']);

    }
}