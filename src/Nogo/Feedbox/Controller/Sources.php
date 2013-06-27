<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feedbox\Helper\Fetcher;
use Nogo\Feedbox\Repository\Source as SourceRepository;
use Nogo\Feedbox\Repository\Item as ItemRepository;

class Sources extends AbstractRestController
{
    /**
     * @var SourceRepository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $fields = [
        'id' => [
            'read' => true,
            'write' => false
        ],
        'name' => [
            'read' => true,
            'write' => true
        ],
        'uri' => [
            'read' => true,
            'write' => true
        ],
        'icon' => [
            'read' => true,
            'write' => false
        ],
        'active' => [
            'read' => true,
            'write' => true
        ],
        'unread' => [
            'read' => true,
            'write' => false
        ],
        'errors' => [
            'read' => true,
            'write' => false
        ],
        'period' => [
            'read' => true,
            'write' => true
        ],
        'last_update' => [
            'read' => true,
            'write' => true
        ],
        'tag_id' => [
            'read' => true,
            'write' => true
        ],
        'created_at' => [
            'read' => true,
            'write' => false
        ],
        'updated_at' => [
            'read' => true,
            'write' => false
        ]
    ];

    public function enable()
    {
        $this->app->get('/sources', array($this, 'listAction'));
        $this->app->get('/sources/:id', array($this, 'getAction'))->conditions(['id' => '\d+']);
        $this->app->post('/sources', array($this, 'postAction'));
        $this->app->put('/sources/:id', array($this, 'putAction'))->conditions(['id' => '\d+']);
        $this->app->delete('/sources/:id', array($this, 'deleteAction'))->conditions(['id' => '\d+']);


        $this->app->get('/update', array($this, 'updateAllAction'));
        $this->app->get('/update/:id', array($this, 'updateAction'))->conditions(['id' => '\d+']);
    }

    /**
     * @return SourceRepository
     */
    public function getRepository(AbstractConnection $connection = null)
    {
        if ($this->repository == null) {
            if ($connection == null) {
                $connection = $this->connection;
            }
            $this->repository = new SourceRepository($connection);
        }
        return $this->repository;
    }

    public function getApiDefinition()
    {
        return $this->fields;
    }

    public function updateAllAction()
    {
        $sources = $this->getRepository()->fetchAllActiveWithUri();

        $result = array();
        foreach($sources as $source) {
            if(isset($source['uri'])) {
                $result[] = $this->fetchSource($source);
            }
        }

        // output updated sources
        $this->renderJson($result);
    }

    public function updateAction($id)
    {
        $id = intval($id);

        $source = $this->getRepository()->fetchOneById($id);

        if ($source === false) {
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
        $this->renderJson($source, $status);
    }

    /**
     * @param array $source
     * @return array
     */
    protected function fetchSource(array $source)
    {
        $itemRepository = new ItemRepository($this->connection);

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
                    $dbItem = $itemRepository->fetchOneBy('uid', $item['uid']);
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
                $itemRepository->persist($item);
            }
        }


        $source['last_update'] = date('Y-m-d H:i:s');
        if (empty($source['period'])) {
            $source['period'] = $worker->getUpdateInterval();
        }
        $source['errors'] = $worker->getErrors();
        $source['unread'] = $itemRepository->countUnread([$source['id']]);
        $this->getRepository()->persist($source);

        return $source;
    }
}