<?php
namespace Nogo\Feedbox\Helper;

/**
 * Class OpmlLoader
 *
 * @package Nogo\Feedbox\Helper
 */
class OpmlLoader
{
    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    /**
     * @return \SimpleXMLElement
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * @param \SimpleXMLElement $xml
     */
    public function setXml(\SimpleXMLElement $xml)
    {
        $this->xml = $xml;

        return $this;
    }

    public function setContent($content)
    {
        $this->xml = new \SimpleXMLElement($content);

        return $this;
    }

    /**
     * Process the xml content into a array of source
     *
     * @return array
     */
    public function run()
    {
        $result = array();
        if (!empty($this->xml)) {
            $outlines = $this->xml->xpath("//outline[@type='rss']");

            /**
             * @var $outline \SimpleXMLElement
             */
            foreach ($outlines as $outline) {
                $attr = $outline->attributes();
                $result[] = array(
                    'name' => $attr['title'],
                    'uri' => $attr['xmlUrl'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );
            }
        }
        return $result;
    }
}