<?php

namespace Nogo\Feedbox\Feed\Worker;

use Nogo\Feedbox\Feed\Worker;
use Zend\Feed\Reader\Entry\EntryInterface;
use Zend\Feed\Reader\Feed\AbstractFeed;
use Zend\Validator\Uri;

/**
 * Class Rss
 * @package Nogo\Feedbox\Helper\Worker
 */
class Rss implements Worker
{
    /**
     * @var AbstractFeed
     */
    protected $feed;

    /**
     * Set feed to process
     *
     * @param AbstractFeed $feed
     * @return $this|Worker
     */
    public function setFeed(AbstractFeed $feed)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Execute RssWorker
     *
     * @return array
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->feed == null) {
            throw new \Exception();
        }

        $result = array();

        /**
         * @var $entry EntryInterface
         */
        foreach ($this->feed as $entry) {
            $uid = md5($entry->getId());

            $title = htmlspecialchars_decode($entry->getTitle());
            $title = htmLawed($title, array("deny_attribute" => "*", "elements" => "-*"));
            if (strlen(trim($title)) == 0) {
                $title = "[ no title ]";
            }

            $link = htmLawed($entry->getLink(), array("deny_attribute" => "*", "elements" => "-*"));

            $content = htmLawed(
                htmlspecialchars_decode($entry->getContent()),
                array(
                    "safe" => 1,
                    "deny_attribute" => '* -alt -title -src -href',
                    "keep_bad" => 0,
                    "comment" => 1,
                    "cdata" => 1,
                    "elements" => 'div,p,ul,li,a,dl,dt,h1,h2,h3,h4,h5,h6,ol,br,table,tr,td,blockquote,pre,ins,del,th,thead,tbody,b,i,strong,em,tt'
                )
            );

            $result[] = array(
                'title' => $title,
                'content' => $content,
                'uid' => $uid,
                'uri' => $link,
                'pubdate' => $this->formatDate($entry->getDateModified()),
                'created_at' => $this->formatDate($entry->getDateCreated()),
                'updated_at' => $this->formatDate($entry->getDateModified())
            );

        }

        return $result;
    }

    protected function formatDate($date)
    {
        if ($date instanceof \DateTime) {
            return $date->format("Y-m-d H:i:s");
        }

        if ($date == null) {
            $date = date('Y-m-d H:i:s');
        }

        return $date;
    }
}