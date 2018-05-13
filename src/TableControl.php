<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 3.2.17
 * Time: 20:37
 */

namespace Nuttilea\TableControl;

use Nuttilea\TableControl\ActionControl\Action;
use Nuttilea\TableControl\ActionControl\CustomAction;
use \Nette\Application\UI\Form;
use Nette\Localization\ITranslator;

/**
 * TODO: Inline edit -> custom update, how to get Form input, how to set Form input type -> DONE
 *
 */
class TableControl extends \Nette\Application\UI\Control implements IFilterAdd {

    /**
     * @var
     */
    protected $primary;

    /** @var TableColumn[] */
    protected $columns = [];

    /**
     * @var array
     */
    protected $filterItems = [];

    /** @var  Form */
    public $form;

    /* OK takze filter musi byt persistentni a musi jej upravovat odeslany formular */
    /**
     * @var bool
     */
    public $ajax = false;

    /**
     * @var
     */
    private $itemsPerPage = 20;

    /**
     * @var null @persistent
     */
    public $page = null;

    /** @persistent */
    public $filter = null;
    const EDIT = ":edit";
    const DELETE = ":delete";
    const DETAIL = ":detail";
    /** @var ITableConnector */
    protected $iTableConnector;
    /** @var Action[] */
    public $actions = [];

    /**
     * @var array
     */
    public $items = [];

    /**
     * @var array
     */
    protected $currentFilter = [];

    /** @persistent */
    public $order = null;

    /** @persistent */
    public $orderby = null;

    protected $latteTemplate = '/table.latte';

    private $inlineEditable = true;
    private $inlineEditRow;


    /** @var  ITranslator */
    private $translator;

    /**
     * TableControl constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->form = $this->createForm();
    }

    /**
     * @return ITranslator
     */
    protected function getTranslator() {
        return $this->translator;
    }


    public function setItemsPerPage($itemsPerPage) {
        $this->itemsPerPage = $itemsPerPage;
    }


    /**
     * @param ITableConnector $iTableConnector
     */
    public function setITableConnector(ITableConnector $iTableConnector) {
        $this->iTableConnector = $iTableConnector;
    }


    public function addAction($name, $actionLatte, \Nette\Application\UI\Link $link, $paramKey = 'id') {
        $action = Action::createAction($actionLatte, $link, $paramKey);
        $this->actions[$name] = $action;
        return $action;
    }


    /**
     * @param CustomAction|callable $customAction
     */
    public function addCustomAction($name, $customAction) {
        if (!$customAction instanceof \Nette\Application\UI\Component && !is_callable($customAction)) {
            throw new \BadMethodCallException("Custom action must be callable or instance of Component");
        }
        $this->actions[$name] = $customAction;
    }


    /**
     * @return \Nette\Application\UI\Multiplier
     */
    public function createComponentAction() {
        $actions = $this->actions;
        return new \Nette\Application\UI\Multiplier(function ($actionId) use ($actions) {
            $action = $actions[$actionId];
            if (is_callable($action)) {
                return (new CustomAction)->setOnRender($action);
            } elseif (!$action instanceof \Nette\Application\UI\Control) {
                throw new \Exception("Custom action must be callable or component");
            }
            return $action;
        });
    }


    /**
     * @param bool $ajax
     */
    public function setAjax($ajax = true) {
        $this->ajax = $ajax;
    }


    public function beforeRender() {
        $this->template->setTranslator($this->getTranslator());
    }


    public function render() {
        $this->beforeRender();
        $template = $this->template;
        $template->setFile(__DIR__ . $this->latteTemplate);
        //Filter prepare
        $this->createCurrentFilter(json_decode($this->filter));
        if ($this->filter) {
            $filter = (array)json_decode($this->filter);
            foreach ($this->filterItems as $key => $formItem) {
                if ($filter && array_key_exists($key, $filter)) {
                    $formItem->setValue($filter[$key]);
                }
            }
        }
        //Pagination
        $page = $this->getParameter('page', 1);
        $paginate = new \Nette\Utils\Paginator();
        $paginate->setItemCount($this->iTableConnector->itemsCount($this->currentFilter));
        $paginate->setItemsPerPage($this->itemsPerPage);
        $paginate->setPage($page);
        //Template variables prepare
        $template->items = $this->iTableConnector->findAll('*', $this->currentFilter, $this->orderby, $this->order, $paginate->getItemsPerPage(), $paginate->getOffset());
        $template->columns = $this->columns;
        $this->template->actions = $this->actions;
        $this->template->filterExists = count($this->filterItems) > 0;
        $this->template->ajax = $this->ajax;
        $this->template->paginate = $paginate;
        $this->template->primary = $this->primary;
//        $this->template->inlineEditRow = $this->inlineEditRow;
        $template->render();
    }


//
    public function handlePage($page = 0){
        $this->page = $page;
        if ($this->presenter->isAjax()) {
            $this->redrawControl('wholetable');
        }
    }

    public function handleSort() {
        if ($this->presenter->isAjax()) {
            $this->redrawControl('wholetable');
        }
    }


    public function setInlineEditable($inlineEditable = true, $rowKeys = ['id']) {
        $this->inlineEditable = $inlineEditable;
        $this->actions[] = Action::createAction(Action::EDIT, $this->lazyLink('inlineEdit!'), $rowKeys)->setClass('ajax');
        return $this->form['inlineEdit'];
    }


    public function handleInlineEdit($rowId) {
        $this->template->inlineEditRow = $this->inlineEditRow = $rowId;
        if ($this->presenter->isAjax()) {
            $this->redrawControl('wholetable');
        }
    }


    /**
     * @return Form
     */
    protected function createForm() {
        $form = new Form();

        $filter = $form->addContainer('filter');
        $filter->addSubmit('filter', 'Filter');

        $inline = $form->addContainer('inlineEdit');
        $inline->addHidden('rowId');
        $inline->addSubmit('save', 'Save');

        $form->setMethod('POST');
        $form->onSuccess[] = array($this, 'onFormSubmitted');
        return $form;
    }


    /**
     * @param $form
     */
    public function onFormSubmitted(Form $form) {
        $values = $form->getValues(true);
        if ($form['filter']['filter']->isSubmittedBy()) {
            $filterValues = $values['filter'];
            $filter = array_filter($filterValues, function ($item) {
                return $item !== "" && $item !== null ? true : false;
            });
            $this->createCurrentFilter($filter, true);
        } elseif ($form['inlineEdit']['save']->isSubmittedBy()) {
            $rId = $values['inlineEdit']['rowId'];
            unset($values['inlineEdit']['rowId']);
            $this->iTableConnector->update($values['inlineEdit'], [$this->primary => $rId]);
        }

        $this->redrawControl('wholetable');
    }


    /**
     * @return Form
     */
    public function createComponentFilter() {
        $inline = $this->form['inlineEdit'];
        foreach ($this->columns as $column) {
            if ($column->isInlineEditable()) {
                $inline->addText($column->name);
            }
        }
        return $this->form;
    }


    /**
     * @param      $filter
     * @param bool $force
     * @return array
     */
    protected function createCurrentFilter($filter, $force = false) {
        if ($filter && (!$this->currentFilter || $force)) {
            foreach ($filter as $key => $value) {
                $column = $this->columns[$key];
                if ($column->getFilterType() == TableColumn::$TEXT) {
                    $this->currentFilter[] = $this->iTableConnector->textModifier($column->aliasFor ? $column->aliasFor : $column->name, $value);
                } else if ($column->getFilterType() == TableColumn::$NUMBER) {
                    $this->currentFilter[] = $this->iTableConnector->numberModifier($column->aliasFor ? $column->aliasFor : $column->name, $value);
                } else if ($column->getFilterType() == TableColumn::$SELECT) {
                    $this->currentFilter[] = $this->iTableConnector->selectModifier($column->aliasFor ? $column->aliasFor : $column->name, $value);
                }
            }
            $this->filter = $filter ? json_encode($filter) : null;
        }


        if (!$this->filter || !$filter) {
            $this->filter = null;
        }
        return $this->currentFilter;
    }


    /**
     * @param TableColumn $column
     * @return \Nette\Forms\Controls\SelectBox|\Nette\Forms\Controls\TextInput|null
     */
    public function onFilterAdded(TableColumn $column) {
        $item = null;

        $formFilter = $this->form->getComponent('filter');
        if ($column->getFilterType() === TableColumn::$TEXT || $column->getFilterType() == TableColumn::$NUMBER) {
            $item = $formFilter->addText($column->name, $column->label);
        } else if ($column->getFilterType() === TableColumn::$SELECT) {
            $item = $formFilter->addSelect($column->name, $column->label);
        }
        return $this->filterItems[$column->name] = $item;
    }


    /**
     * @param $primary
     */
    public function setPrimaryKey($primary) {
        $this->primary = $primary;
    }


    /**
     * @param $column | String
     * @return TableColumn
     */
    public function addColumn($column, $name = null) {
        if (!$name) $name = $column;
        if ($this->translator) {
            $name = $this->getTranslator()->translate($name);
        }
        return $this->columns[$column] = new TableColumn($column, $name, $this);
    }


    /**
     * @return ITableConnector
     */
    public function getITableConnector() {
        return $this->iTableConnector;
    }


    /**
     * @return mixed
     */
    public function getPrimary() {
        return $this->primary;
    }


    /**
     * @return TableColumn[]
     */
    public function getColumns() {
        return $this->columns;
    }


    /**
     * @param $column
     * @return TableColumn
     */
    public function getColumn($column) {
        return $this->columns[$column];
    }

}