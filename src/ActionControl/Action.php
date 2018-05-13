<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 9.2.17
 * Time: 22:17
 */

namespace Nuttilea\TableControl\ActionControl;
/**
 * Actions - model / view ?
 **/
class Action extends \Nette\Application\UI\Control {

    const EDIT = __DIR__ . "/edit.latte";
    const EDIT_MODAl = __DIR__ . "/edit_modal.latte";
    const DELETE = __DIR__ . "/delete.latte";
    const DETAIL = __DIR__ . "/detail.latte";
    const APPROVE = __DIR__ . "/approve.latte";
    const DENY = __DIR__ . "/deny.latte";
    const EDITORDER = __DIR__ . "/editorder.latte";

    private $latte = self::EDIT;

    /** @var \Nette\Application\UI\Link */
    private $link = null;

    private $rowKeys = 'rowId';

    private $label = null;

    private $onCreateLink = [];

    private $class = null;

    /** @var  \Nette\Localization\ITranslator */
    private $translator;

    protected function getTranslator() {
        return $this->translator;
    }


    /**
     * @param null $class
     * @return Action
     */
    public function addClass($class) {
        if (!$this->class) $this->class = '';
        $this->class .= $class . ' ';
        return $this;
    }

    /**
     * @param \Nette\Application\UI\Link $link
     * @param string $rowKeys - ['rowKey' => 'linkKey'], [rowKey1, rowKey2]
     */
    public function setLink(\Nette\Application\UI\Link $link, $rowKeys = ['id']) {
        $this->link = $link;
        $this->rowKeys = $rowKeys;
    }


    public function setLatte($latte) {
        $this->latte = $latte;
    }


    public function setLabel($label) {
        $this->label = $label;
    }

    public function setOnCreateLink($callback) {
        $this->onCreateLink[] = is_callable($callback) ? $callback : null;
    }

    public function render($row, $key) {
        $link = clone $this->link;
        $template = $this->template;
        $template->setFile($this->latte);
        $template->setTranslator($this->getTranslator());

        $template->class = $this->class;
        if (!$key) $key = $this->rowKeys;

        if (!empty($this->onCreateLink)) {
            $this->onCreateLink[0]($row, $link);
        } else {
            if (is_array($key)) {
                foreach ($key as $rowKey => $linkKey) {
                    if (key_exists($rowKey, $row)) $link->setParameter($linkKey, $row[$rowKey]);
                }
            } else {
                $link->setParameter($this->rowKeys, $row[$key]);
            }
        }
        $template->link = $link;
        $template->label = $this->label;
        $template->render();
    }


    public static function createAction($actionLatte, \Nette\Application\UI\Link $link, $rowKeys = ['id']) {
        $action = new Action;
        $action->setLink($link, $rowKeys);
        $action->setLatte($actionLatte);
        return $action;
    }

}