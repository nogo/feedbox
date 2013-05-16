<?php
namespace Nogo\Feed\Helper;

use Nogo\Feed\Repository\Item as ItemRepository;
use Nogo\Feed\Repository\Source as SourceRepository;
use Slim\Slim;

class FeedLoader
{
    protected $cacheDir;
    /**
     * @var ItemRepository
     */
    protected $itemRepository;
    /**
     * @var array
     */
    protected $source;
    /**
     * @var SourceRepository
     */
    protected $sourceRepository;

    public function setCacheDir($path)
    {
        $this->cacheDir = $path;
    }

    public function setItemRepository(ItemRepository $repository)
    {
        $this->itemRepository = $repository;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource(array $source)
    {
        $this->source = $source;
    }

    public function setSourceRepository(SourceRepository $repository)
    {
        $this->sourceRepository = $repository;
    }

    public function run()
    {
        if (!empty($this->source)) {
            $data = trim($this->readUrl($this->source['uri']));
            if ($data != null && !empty($data)) {
                try {
                    \Zend\Feed\Reader\Reader::registerExtension('Syndication');
                    $feed = \Zend\Feed\Reader\Reader::importString($data);
                    $this->source['errors'] = '';
                } catch (\Zend\Feed\Reader\Exception\InvalidArgumentException $ex) {
                    $this->source['errors'] = $ex->getMessage();
                    $feed = null;
                } catch (\Zend\Feed\Reader\Exception\RuntimeException $ex) {
                    $this->source['errors'] = $ex->getMessage();
                    $feed = null;
                }

                /**
                 * @var $entry \Zend\Feed\Reader\Entry\EntryInterface
                 */
                if ($feed != null) {

                    $linkValidator = new \Zend\Validator\Uri();

                    foreach ($feed as $entry) {
                        $uid = md5($entry->getId());
                        $dbItem = $this->itemRepository->fetchOneBy('uid', $uid);
                        if (!empty($dbItem)) {
                            // TODO UPDATE ?
                            continue;
                        }

                        $title = htmlspecialchars_decode($entry->getTitle());
                        $title = htmLawed($title, array("deny_attribute" => "*", "elements" => "-*"));
                        if (strlen(trim($title)) == 0) {
                            $title = "[ no title ]";
                        }

                        $link = null;
                        if ($linkValidator->isValid($entry->getLink())) {
                            $link = htmLawed($entry->getLink(), array("deny_attribute" => "*", "elements" => "-*"));
                        }

                        $entity = array(
                            'title' => $title,
                            'content' => htmLawed(
                                htmlspecialchars_decode($entry->getContent()),
                                array(
                                    "safe" => 1,
                                    "deny_attribute" => '* -alt -title -src -href',
                                    "keep_bad" => 0,
                                    "comment" => 1,
                                    "cdata" => 1,
                                    "elements" => 'div,p,ul,li,a,img,dl,dt,h1,h2,h3,h4,h5,h6,ol,br,table,tr,td,blockquote,pre,ins,del,th,thead,tbody,b,i,strong,em,tt'
                                )
                            ),
                            'source_id' => $this->source['id'],
                            'uid' => $uid,
                            'uri' => $link,
                            'pubdate' => $this->formatDate($entry->getDateModified()),
                            'created_at' => $this->formatDate($entry->getDateCreated()),
                            'updated_at' => $this->formatDate($entry->getDateModified())
                        );

                        $this->itemRepository->persist($entity);
                    }

                    $syndication = $feed->getExtension('Syndication');
                    $this->source['period'] = $syndication->getUpdatePeriod();

                    unset($feed);
                }

                $this->source['last_update'] = date('Y-m-d H:i:s');
                $this->source['unread'] = $this->itemRepository->countUnread([$this->source['id']]);
                $this->sourceRepository->persist($this->source);
            }
        }
    }

    protected function readUrl($url)
    {
        $result = null;
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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