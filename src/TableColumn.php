<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 3.2.17
 * Time: 21:14
 */
namespace Nutillea\TableView;

class TableColumn {

    public static $TEXT = 'text';
    public static $NUMBER = 'number';
    public static $SELECT = 'select';

    public $label;
    public $name;
    public $aliasFor;
    public $isSortable = true;

    protected $onRender = null;

    protected $filterType;

    /** @var  IFilterAdd */
    private $filterAdd;

    private $isInlineEditable = false;

    public function __construct($name, $label, IFilterAdd $filterAdd) {
        $this->label = $label;
        $this->name = $name;
        $this->filterAdd = $filterAdd;
    }


    public function setSortable($isSortable = true) {
        $this->isSortable = $isSortable;
        return $this;
    }

    public function setInlineEditable($inlineEditable = true){
        $this->isInlineEditable = $inlineEditable;
    }

    public function isInlineEditable() {
        return $this->isInlineEditable;
    }

    public function isSortable() {
        return $this->isSortable;
    }

    /** @return \Nette\Forms\Controls\BaseControl */
    public function addFilter($filter = 'text', $col=null) {
        $this->filterType = $filter;
        $this->aliasFor = $col;
        return $this->filterAdd->onFilterAdded($this);
    }


    public function getFilterType() {
        return $this->filterType;
    }


    public function modifyCellValue($row) {
        $callback = $this->onRender;
        if ($callback && is_callable($callback)) {
            return $callback($row, $this->name);
        } else {
            return $row[$this->name];
        }
    }


    public function addOnRenderListener($callback) {
        if (is_callable($callback)) {
            $this->onRender = $callback;
        } else {
            throw new \Exception('$callback is not callable');
        }
        return $this;
    }



}