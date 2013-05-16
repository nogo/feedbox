<?php
namespace Nogo\Feed\Controller;

use Aura\Sql\Connection\AbstractConnection;
use Nogo\Feed\Helper\FeedLoader;
use Nogo\Feed\Repository\Repository;
use Nogo\Feed\Repository\Source as SourceRepository;
use Nogo\Feed\Repository\Item as ItemRepository;

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
        $result = $this->getRepository()->fetchAll();

        $feedRunner = new FeedLoader();
        $feedRunner->setCacheDir($this->app->config('cache_dir'));
        $feedRunner->setSourceRepository($this->getRepository());
        $feedRunner->setItemRepository(new ItemRepository($this->connection));

        foreach($result as $source) {
            if(isset($source['uri'])) {
                $feedRunner->setSource($source);
                $feedRunner->run();
            }
        }

        $this->render('OK');
    }

    public function updateAction($id)
    {
        $id = intval($id);

        $result = $this->getRepository()->fetchOneById($id);

        if ($result === false) {
            $this->render('Not found', 404);
            return;
        }

        $feedRunner = new FeedLoader();
        $feedRunner->setCacheDir($this->app->config('cache_dir'));
        $feedRunner->setSourceRepository($this->getRepository());
        $feedRunner->setItemRepository(new ItemRepository($this->connection));
        $feedRunner->setSource($result);
        $feedRunner->run();
//
//        $this->render('OK');
    }
}