<?php
namespace Nogo\Feedbox\Helper;

use Zend\Validator\Uri;
use Zend\Feed\Reader\Reader;
use Zend\Feed\Reader\Feed\AbstractFeed;
use Zend\Feed\Reader\Entry\EntryInterface;
use Zend\Feed\Reader\Exception\InvalidArgumentException;
use Zend\Feed\Reader\Exception\RuntimeException;
use Zend\Feed\Reader\Extension\Syndication\Feed as Syndication;

/**
 * Class FeedLoader
 * @package Nogo\Feedbox\Helper
 */
class FeedLoader
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $update_interval;

    /**
     * @var string
     */
    protected $errors;


    /**
     * @var int
     */
    protected $timeout = 10;

    /**
     * @param string $update_interval
     *
     * @return $this
     */
    public function setUpdateInterval($update_interval)
    {
        $this->update_interval = $update_interval;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdateInterval()
    {
        return $this->update_interval;
    }

    /**
     * Set uri
     *
     * @param string $uri
     *
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    public function run()
    {
        $result = array();

        if (!empty($this->uri)) {
            $data = trim($this->readUrl($this->uri));
            if ($data != null && !empty($data)) {
                try {
                    Reader::registerExtension('Syndication');
                    $feed = Reader::importString($data);
                    $this->errors = '';
                } catch (InvalidArgumentException $ex) {
                    $this->errors = $ex->getMessage();
                    $feed = null;
                } catch (RuntimeException $ex) {
                    $this->errors = $ex->getMessage();
                    $feed = null;
                }

                /**
                 * @var $feed  AbstractFeed
                 * @var $entry EntryInterface
                 */
                if ($feed != null) {

                    $linkValidator = new Uri();

                    foreach ($feed as $entry) {
                        $uid = md5($entry->getId());

                        $title = htmlspecialchars_decode($entry->getTitle());
                        $title = htmLawed($title, array("deny_attribute" => "*", "elements" => "-*"));
                        if (strlen(trim($title)) == 0) {
                            $title = "[ no title ]";
                        }

                        $link = null;
                        if ($linkValidator->isValid($entry->getLink())) {
                            $link = htmLawed($entry->getLink(), array("deny_attribute" => "*", "elements" => "-*"));
                        }

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

                    /**
                     * @var $syndication Syndication
                     */
                    $syndication = $feed->getExtension('Syndication');
                    $this->update_interval = $syndication->getUpdatePeriod();

                    unset($feed);
                }
            }
        }
        return $result;
    }

    protected function readUrl($url)
    {
        $result = null;
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
            $result = $this->curl_exec_follow($ch);
            curl_close($ch);
        } else {
            $result = file_get_contents($url);
        }
        return $result;
    }

    protected function curl_exec_follow( /*resource*/
        $ch, /*int*/
        &$maxredirect = null
    ) {
        $mr = $maxredirect === null ? 5 : intval($maxredirect);
        if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            if ($mr > 0) {
                $newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

                $rch = curl_copy_handle($ch);
                curl_setopt($rch, CURLOPT_HEADER, true);
                curl_setopt($rch, CURLOPT_NOBODY, true);
                curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
                curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($rch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
                do {
                    curl_setopt($rch, CURLOPT_URL, $newurl);
                    $header = curl_exec($rch);
                    if (curl_errno($rch)) {
                        $code = 0;
                    } else {
                        $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                        if ($code == 301 || $code == 302) {
                            preg_match('/Location:(.*?)\n/', $header, $matches);
                            $newurl = trim(array_pop($matches));
                        } else {
                            $code = 0;
                        }
                    }
                } while ($code && --$mr);
                curl_close($rch);
                if (!$mr) {
                    if ($maxredirect === null) {
                        trigger_error(
                            'Too many redirects. When following redirects, libcurl hit the maximum amount.',
                            E_USER_WARNING
                        );
                    } else {
                        $maxredirect = 0;
                    }
                    return false;
                }
                curl_setopt($ch, CURLOPT_URL, $newurl);
            }
        }
        return curl_exec($ch);
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