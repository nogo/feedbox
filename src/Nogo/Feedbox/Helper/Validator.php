<?php
namespace Nogo\Feedbox\Helper;

class Validator
{
    public static function datetime($value)
    {
        $result = false;
        try {
            if ($value == null) {
                $result = null;
            } else {
                $dt = new \DateTime($value);
                $result = $dt->format("Y-m-d H:i:s");
            }
        } catch (\Exception $e) {

        }

        return $result;
    }
}