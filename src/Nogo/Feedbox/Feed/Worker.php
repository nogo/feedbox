<?php
namespace Nogo\Feedbox\Feed;

use Nogo\Feedbox\Helper\Sanitizer;

/**
 * Class Worker
 * @package Nogo\Feedbox\Feed
 */
interface Worker
{
    /**
     * Set content for worker
     *
     * @param string $content
     * @return Worker
     */
    public function setContent($content);

    /**
     * @param Sanitizer $sanitier
     * @return Worker
     */
    public function setSanitizer(Sanitizer $sanitier);

    /**
     * Return worker errors
     *
     * @return mixed
     */
    public function getErrors();

    /**
     * @return string
     */
    public function getUpdateInterval();

    /**
     * Execute worker
     *
     * @return array
     * @throws \Exception
     */
    public function execute();
}