<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 7/12/18
 * Time: 6:54 PM
 */

namespace Nuttilea\TableControl;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Utils\Paginator;

class ViewControl extends Control {

    /** @var array */
    public $items = [];

    /** @var null @persistent */
    public $page = null;

    /** @var integer */
    protected $itemsPerPage = 20;

    /** @var ITableConnector */
    protected $iTableConnector;

    /** @var $paginator Paginator */
    private $paginator;

    /** @var  ITranslator */
    protected $translator = null;

    protected $currentFilter = [];

    /** @persistent */
    public $order = null;

    /** @persistent */
    public $orderby = null;

    /**
     * @var bool
     */
    public $ajax = false;

    protected $latteTemplate = __DIR__.'/templates/table.latte';

    public function render(){

    }

    /**
     * @param bool $ajax
     */
    public function setAjax($ajax = true) {
        $this->ajax = $ajax;
    }

    /**
     * @param ITableConnector $iTableConnector
     */
    public function setITableConnector(ITableConnector $iTableConnector) {
        $this->iTableConnector = $iTableConnector;
    }

    public function getPaginator(){
        if(!$this->paginator){
            $this->paginator = new \Nette\Utils\Paginator();
            $this->paginator->setItemCount($this->getItemsCount());
            $this->paginator->setItemsPerPage($this->itemsPerPage);
            $this->paginator->setPage($this->getCurrentPage());
        }
        return $this->paginator;
    }

    public function getItemsCount(){
        return $this->iTableConnector->itemsCount($this->currentFilter);
    }

    public function getCurrentPage(){
        return $this->page ? $this->page : 1;
    }

    public function attached($presenter) {
        parent::attached($presenter);
        $this->template->setTranslator($this->getTranslator());
        $paginate = $this->getPaginator();
        $this->items = $this->iTableConnector->findAll('*', $this->currentFilter, $this->orderby, $this->order, $paginate->getItemsPerPage(), $paginate->getOffset());
    }

    /**
     * @return ITranslator
     */
    protected function getTranslator() {
        return $this->translator ? $this->translator : $this->translator = new EmptyTranslator();
    }
}