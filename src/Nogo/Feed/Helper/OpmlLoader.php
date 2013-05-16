<?php
namespace Nogo\Feed\Helper;

use Nogo\Feed\Repository\Item as ItemRepository;
use Nogo\Feed\Repository\Source as SourceRepository;
use Slim\Slim;

class OpmlLoader
{
    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    /**
     * @var SourceRepository
     */
    protected $sourceRepository;

    public function setXml(\SimpleXMLElement $xml)
    {
        $this->xml = $xml;
    }

    public function setContent($content)
    {
        $this->xml = new \SimpleXMLElement($content);
    }

    public function setSourceRepository(SourceRepository $repository)
    {
        $this->sourceRepository = $repository;
    }

    public function run()
    {
        if (!empty($this->xml)) {
            $outlines = $this->xml->xpath("//outline[@type='rss']");

            /**
             * @var $outline \SimpleXMLElement
             */
            foreach($outlines as $outline) {
                $attr = $outline->attributes();
                $source = [
                    'name' => $attr['title'],
                    'uri' => $attr['xmlUrl'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->sourceRepository->persist($source);
            }
        }
    }
}