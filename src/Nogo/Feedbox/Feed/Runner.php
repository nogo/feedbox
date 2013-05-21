<?php
namespace Nogo\Feedbox\Feed;

use Zend\Feed\Reader\Exception\InvalidArgumentException;
use Zend\Feed\Reader\Exception\RuntimeException;
use Zend\Feed\Reader\Extension\Syndication\Feed as Syndication;
use Zend\Feed\Reader\Feed\AbstractFeed;
use Zend\Feed\Reader\Reader;
use Zend\Validator\Uri;

/**
 * Class Runner
 * @package Nogo\Feedbox\Helper
 */
class Runner
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
     * @var Worker
     */
    protected $worker = null;

    /**
     * @return string
     */
    public function getUpdateInterval()
    {
        return $this->update_interval;
    }

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
    public function getUri()
    {
        return $this->uri;
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
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $errors
     * @return $this
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return Worker
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * @param Worker $worker
     * @return $this
     */
    public function setWorker(Worker $worker)
    {
        $this->worker = $worker;

        return $this;
    }

    /**
     * Execute runner
     *
     * @return array|null
     * @throws \Exception
     */
    public function run()
    {
        if ($this->worker == null) {
            throw new \Exception("Runner has no worker.");
        }

        if (!isset($this->uri) || empty($this->uri)) {
            throw new \Exception("Runner has no uri to process.");
        }

        $result = null;

        // fetch data from uri
        $data = $this->readUrl($this->uri);
        if ($data == null) {
            $this->errors = 'Connection timeout.';
        } else {
            $data = trim($data);
            if (empty($data)) {
                $this->errors = 'Source content is empty.';
            } else {
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
                 */
                if ($feed != null) {
                    /**
                     * @var $syndication Syndication
                     */
                    $syndication = $feed->getExtension('Syndication');
                    $this->update_interval = $syndication->getUpdatePeriod();

                    $this->worker->setFeed($feed);
                    $result = $this->worker->execute();

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
            $error = curl_errno($ch);
            curl_close($ch);
            if ($error > 0) {
                $result = null;
            }
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
}