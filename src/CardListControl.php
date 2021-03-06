<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 3.2.17
 * Time: 20:37
 */

namespace Nuttilea\TableControl;

use Nette\Application\UI\ITemplateFactory;
use Nette\Application\UI\Multiplier;
use Nette\Utils\Random;
use Nuttilea\TableControl\ActionControl\Action;
use Nuttilea\TableControl\ActionControl\CustomAction;
use \Nette\Application\UI\Form;
use Nette\Localization\ITranslator;

class CardListControl extends ViewControl {


    /** @var callable */
    protected $cardRenderer;

    /**
     * CardListControl constructor.
     */
    public function __construct(callable $onCardRenderCallback = null) {
        parent::__construct();
        $this->cardRenderer = $onCardRenderCallback;
    }

    public function attached($presenter) {
        parent::attached($presenter); // TODO: Change the autogenerated stub
        $this->latteTemplate = __DIR__.'/templates/cardListView.latte';
    }


    private function getCardRender(){
        return $this->cardRenderer ? $this->cardRenderer : function(){return new CardControl();};
    }

    public function onCardRender($name, $parent){
        return call_user_func($this->cardRenderer, $this->items[$name]);
    }

    public function createComponent($name){
        return new Multiplier([$this, 'onCardRender']);
    }

    public function render() {
        parent::render(); // TODO: Change the autogenerated stub
        $this->template->setFile($this->latteTemplate);
        //Filter prepare
//        $this->createCurrentFilter(json_decode($this->filter));
//        if ($this->filter) {
//            $filter = (array)json_decode($this->filter);
//            foreach ($this->filterItems as $key => $formItem) {
//                if ($filter && array_key_exists($key, $filter)) {
//                    $formItem->setValue($filter[$key]);
//                }
//            }
//        }
        //Template variables prepare
        $this->template->items = $this->items;
//        $this->template->columns = $this->columns;
//        $this->template->actions = $this->actions;
//        $this->template->filterExists = count($this->filterItems) > 0;
        $this->template->ajax = $this->ajax;
        $this->template->paginate = $this->getPaginator();
        $this->template->render();
    }
}