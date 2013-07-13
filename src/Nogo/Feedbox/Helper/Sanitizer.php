<?php
namespace Nogo\Feedbox\Helper;

interface Sanitizer {

    public function sanitize($html, $config = null);
}