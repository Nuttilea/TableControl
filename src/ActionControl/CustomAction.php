<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 9.2.17
 * Time: 22:17
 */
namespace Nutillea\TableView\ActionControl;


/**
 * Action - model / view ?
 **/
class CustomAction extends \Nette\Application\UI\Control {

    /**
     * @var null
     */
    public $onRender = null;


    /**
     * @param callable $onRender
     * @return CustomAction
     */
    public function setOnRender(callable $onRender) {
        $this->onRender = $onRender;
        return $this;
    }


    /**
     * @param $row
     */
    public function render($row) {
        $template = $this->template;
        $template->setFile(__DIR__ . "/simple_action.latte");
        $callback = $this->onRender;
        $template->toRender = is_callable($this->onRender) ? call_user_func($callback, $row) : null;
        $template->render();
    }

}