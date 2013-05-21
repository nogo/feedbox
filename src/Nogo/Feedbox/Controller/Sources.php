<?php
namespace Nogo\Feedbox\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feedbox\Helper\FeedLoader;
use Nogo\Feedbox\Repository\Repository;
use Nogo\Feedbox\Repository\Source as SourceRepository;
use Nogo\Feedbox\Repository\Item as ItemRepository;

class Sources extends AbstractRestController
{
    /**
     * @var SourceRepository
     */
    protected $repository;

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
     * @return Repository
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

    public function updateAllAction()
    {
        $sources = $this->getRepository()->fetchAllActive();

        $feedRunner = new FeedLoader();

        $result = array();
        foreach($sources as $source) {
            if(isset($source['uri'])) {
                $result[] = $this->fetchSource($source, $feedRunner);
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
        $this->fetchSource($source);

        // output updated source
        $this->renderJson($source);
    }

    protected function fetchSource($source, FeedLoader $runner = null)
    {
        if ($runner == null) {
            $runner = new FeedLoader();
        }

        $runner->setUri($source['uri']);
        $items = $runner->run();

        $itemRepository = new ItemRepository($this->connection);
        foreach($items as $item) {
            if (isset($item['uid'])) {
                $dbItem = $itemRepository->fetchOneBy('uid', $item['uid']);
                // TODO UPDATE ?
                if (!empty($dbItem)) {
                    continue;
                }
            }

            $item['source_id'] = $source['id'];
            $itemRepository->persist($item);
        }

        $source['last_update'] = date('Y-m-d H:i:s');
        $source['period'] = $runner->getUpdateInterval();
        $source['errors'] = $runner->getErrors();
        $source['unread'] = $itemRepository->countUnread([$source['id']]);
        $this->getRepository()->persist($source);

        return $source;
    }
}