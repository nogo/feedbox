<?php
namespace Nogo\Feedbox\Helper;

use HTMLPurifier;
use Nogo\Feedbox\Helper\Sanitizer;

/**
 * Class HtmlPurifierSanitizer
 * @package Nogo\Feedbox\Helper
 */
class HtmlPurifierSanitizer implements Sanitizer {

    /**
     * @var HTMLPurifier
     */
    protected $purifier;

    /**
     * @param HTMLPurifier $purifier
     */
    public function __construct(HTMLPurifier $purifier = null)
    {
        if ($purifier == null) {
            $purifier = new HTMLPurifier();
        }
        $this->purifier = $purifier;
    }

    /**
     * @param $html
     * @param null $config
     * @return \Purified
     */
    public function sanitize($html, $config = null)
    {
        return $this->purifier->purify($html, $config);
    }
}