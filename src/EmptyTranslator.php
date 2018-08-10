<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 7/13/18
 * Time: 10:53 AM
 */

namespace Nuttilea\TableControl;


use Nette\Localization\ITranslator;

class EmptyTranslator implements ITranslator {

    /**
     * Translates the given string.
     * @param  mixed    message
     * @param  int      plural count
     * @return string
     */
    function translate($message, $count = null) {
        return $message;
    }
}