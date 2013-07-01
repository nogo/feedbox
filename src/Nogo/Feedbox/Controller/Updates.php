<?php
namespace Nogo\Feedbox\Controller;

use Nogo\Feedbox\Helper\Fetcher;
use Nogo\Feedbox\Api\Source as SourceApi;
use Nogo\Feedbox\Api\Tag as TagApi;
use Nogo\Feedbox\Repository\Source as SourceRepository;
use Nogo\Feedbox\Repository\Tag as TagRepository;
use Nogo\Feedbox\Repository\Item as ItemRepository;

class Updates extends AbstractController
{
    /**
     * @var SourceRepository
     */
    protected $sourceRepository;

    /**
     * @var SourceApi
     */
    protected $sourceApi;

    /**
     * @var tagRepository
     */
    protected $tagRepository;

    /**
     * @var TagApi
     */
    protected $tagApi;

    /**
     * @var ItemRepository
     */
    protected $itemRepository;

    public function enable()
    {
        $this->app->get('/update', array($this, 'updateAllAction'));
        $this->app->get('/update/:name/:id', array($this, 'updateAction'))
            ->conditions([
                    'name' => 'tag|source',
                    'id' => '\d+'
            ]);

        $this->sourceApi = new SourceApi();
        $this->tagApi = new TagApi();

        $this->sourceRepository = new SourceRepository($this->connection);
        $this->tagRepository = new TagRepository($this->connection);
        $this->itemRepository = new ItemRepository($this->connection);
    }

    public function updateAllAction()
    {
        $sources = $this->sourceRepository->findAllActiveWithUri();

        $result = array();
        foreach($sources as $source) {
            if(isset($source['uri'])) {
                $result[] = $this->fetchSource($source);
            }
        }

        // output updated sources
        $this->renderJson($result);
    }

    public function updateAction($name, $id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        $source = [];
        switch ($name) {
            case 'source':
                $source = $this->sourceRepository->find($id);

                if (empty($source)) {
                    $this->render('Not found', 404);
                    return;
                }

                if (empty($source['uri'])) {
                    $this->render('Source has no URL to fetch.', 404);
                    return;
                }

                // fetch source
                $source = $this->fetchSource($source);

                $status = 200;
                if (!empty($source['errors'])) {
                    $status = 502;  // Bad Gateway
                }

                // output updated source
                $this->renderJson($this->sourceApi->serializeData($source), $status);
                break;
            case 'tag':
                $tag = $this->tagRepository->find($id);

                if (empty($tag)) {
                    $this->render('Not found', 404);
                    return;
                }

                $sources = $this->sourceRepository->findAllBy('tag_id', $tag['id']);

                $result = array();
                foreach($sources as $source) {
                    if(isset($source['uri'])) {
                        $result[] = $this->fetchSource($source);
                    }
                }

                $tag = $this->tagRepository->find($tag['id']);

                // output updated tag
                $this->renderJson($this->tagApi->serializeData($tag));
                break;
        }
    }

    /**
     * @param array $source
     * @return array
     */
    protected function fetchSource(array $source)
    {
        $fetcher = new Fetcher();
        $fetcher->setTimeout($this->app->config('fetcher.timeout'));
        $content = $fetcher->get($source['uri']);

        $defaultWorkerClass = $this->app->config('worker.default');

        /**
         * @var $worker \Nogo\Feedbox\Feed\Worker
         */
        $worker = new $defaultWorkerClass();
        $worker->setContent($content);
        try {
            $items = $worker->execute();
        } catch (\Exception $e) {
            $items = null;
        }

        if ($items != null) {
            foreach($items as $item) {
                if (isset($item['uid'])) {
                    $dbItem = $this->itemRepository->findBy('uid', $item['uid']);
                    if (!empty($dbItem)) {
                        if ($item['content'] !== $dbItem['content']
                            || $item['title'] !== $dbItem['title']) {
                            $item['id'] = $dbItem['id'];
                            $item['starred'] = $dbItem['starred'];
                            $item['created_at']= $dbItem['created_at'];
                        } else {
                            continue;
                        }
                    }
                }

                $item['source_id'] = $source['id'];
                $this->itemRepository->persist($item);
            }
        }

        $source['last_update'] = date('Y-m-d H:i:s');
        if (empty($source['period'])) {
            $source['period'] = $worker->getUpdateInterval();
        }
        $source['errors'] = $worker->getErrors();
        $source['unread'] = $this->itemRepository->countSourceUnread([$source['id']]);
        $this->sourceRepository->persist($source);

        // update tag unread counter
        if (!empty($source['tag_id'])) {
            $tag = $this->tagRepository->find($source['tag_id']);
            if ($tag) {
                $tag['unread'] = $this->sourceRepository->countTagUnread([$tag['id']]);
                $this->tagRepository->persist($tag);
            }
        }

        // clean up double uids in this source
        $uids = $this->itemRepository->findDoubleUid($source['id']);
        foreach ($uids as $uid) {
            $items = $this->itemRepository->findAllBy('uid', $uid);
            for ($i=1; $i<count($items); $i++) {
                $this->itemRepository->remove($items[$i]['id']);
            }
        }

        return $source;
    }
}