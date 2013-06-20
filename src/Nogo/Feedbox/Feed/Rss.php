<?php

namespace Nogo\Feedbox\Feed;

use Nogo\Feedbox\Feed\Worker;
use Zend\Feed\Reader\Entry\EntryInterface;
use Zend\Feed\Reader\Exception\InvalidArgumentException;
use Zend\Feed\Reader\Exception\RuntimeException;
use Zend\Feed\Reader\Extension\Syndication\Feed as Syndication;
use Zend\Feed\Reader\Feed\AbstractFeed;
use Zend\Feed\Reader\Reader;

/**
 * Class Rss
 *
 * @package Nogo\Feedbox\Feed
 */
class Rss implements Worker
{
    /**
     * @var AbstractFeed
     */
    protected $feed;

    /**
     * @var string
     */
    protected $errors;

    /**
     * Set content for worker
     *
     * @param string $content
     * @return Worker
     */
    public function setContent($content)
    {
        if ($content == null) {
            $this->errors = 'Connection timeout';
        } else {
            $content = trim($content);
            if (empty($content)) {
                $this->errors = 'Source content is empty.';
            } else {
                try {
                    Reader::registerExtension('Syndication');
                    $this->feed = Reader::importString($content);
                    $this->errors = '';
                } catch (InvalidArgumentException $ex) {
                    $this->errors = $ex->getMessage();
                    $this->feed = null;
                } catch (RuntimeException $ex) {
                    $this->errors = $ex->getMessage();
                    $this->feed = null;
                }
            }
        }

        return $this;
    }


    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    public function getUpdateInterval()
    {
        /**
         * @var $syndication Syndication
         */
        $syndication = $this->feed->getExtension('Syndication');
        return $syndication->getUpdatePeriod();
    }

    /**
     * Execute Rss
     *
     * @return array
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->feed == null) {
            throw new \Exception("Feed object is null");
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