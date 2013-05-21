<?php
namespace Nogo\Feedbox\Feed;

use Zend\Feed\Reader\Feed\AbstractFeed;

/**
 * Class Worker
 * @package Nogo\Feedbox\Feed
 */
interface Worker
{
    /**
     * Set feet to process
     *
     * @param AbstractFeed $feed
     * @return Worker
     */
    public function setFeed(AbstractFeed $feed);

    /**
     * Execute worker
     *
     * @return array
     * @throws \Exception
     */
    public function execute();
}