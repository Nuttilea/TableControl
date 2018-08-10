<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 9.2.17
 * Time: 22:17
 */

namespace Nuttilea\TableControl\ActionControl;

use Nette\Localization\ITranslator;
use Nuttilea\TableControl\EmptyTranslator;

/**
 * Action - model / view ?
 **/
class Action extends \Nette\Application\UI\Control {
    
    const EDIT       = 'edit';
    const EDIT_MODAl = 'editModal';
    const DELETE     = 'delete';
    const DETAIL     = 'detail';
    const APPROVE    = 'approve';
    const DENY       = 'deny';
    const PLAY       = 'play';
    
    /**
     * Allowed icon/latte/class
     *
     * @var array
     */
    public $icons = [
        self::EDIT       => [
            'icon' => 'fa fa-edit',
        ],
        self::EDIT_MODAl => [
            'latte' => 'modal',
            'icon'  => 'fa fa-magic',
        ],
        self::DELETE     => [
            'icon' => 'fa fa-times text-danger',
            'confirm' => true,
        ],
        self::DETAIL     => [
            'icon' => 'fa fa-eye',
        ],
        self::APPROVE    => [
            'icon' => 'fa fa-check',
        ],
        self::DENY       => [
            'icon' => 'fa fa-minus-circle',
        ],
        self::PLAY       => [
            'icon' => 'fa fa-play text-success',
        ],
    ];
    
    private $latte = null;
    
    private $icon;
    
    /** @var \Nette\Application\UI\Link */
    private $link = null;
    
    private $rowKeys = 'rowId';
    
    private $label = null;
    
    private $onCreateLink = [];
    
    public $onAction = [];
    
    private $class = [];
    
    private $confirm = false;
    
    /** @var  \Nette\Localization\ITranslator */
    private static $translator;
    
    public static function setTranslator(ITranslator $translator){
        self::$translator = $translator;
    }

    protected function getTranslator() {
        return self::$translator ? self::$translator : self::$translator = new EmptyTranslator();
    }
    
    public function getLattePath($latte){
        return __DIR__.'/'.$latte.'.latte';
    }
    
    protected function setAction( $action ) {
        
        if (array_key_exists($action, $this->icons)) {
            $values = $this->icons[ $action ];
            $this->setIcon($values['icon']);
            if (!empty($values['latte'])) {
                $this->setLatte($this->getLattePath($values['latte']));
            }
            
            if (!empty($values['class'])) {
                $this->addClass($values['class']);
            }
            if (!empty($values['confirm'])) {
                $this->setConfirm($values['confirm']);
            }
        }
        
        return false;
    }
    
    protected function setCustomAction( $action) {
        $this->setIcon($action['icon']);
        if (!empty($action['class'])) {
            $this->addClass($action['class']);
        }
        if (!empty($action['latte'])) {
            $this->setLatte($this->getLattePath($action['latte'])); //TODO: Make universal
        }
    }
    
    public function setConfirm($value = false){
        $this->confirm = $value ? true : false;
    }
    /**
     * @param null $class
     *
     * @return Action
     */
    public function addClass( $class ) {
        $this->class[$class] = $class;
        
        return $this;
    }
    
    /**
     * @param \Nette\Application\UI\Link $link
     * @param string                     $rowKeys - ['rowKey' => 'linkKey'], [rowKey1, rowKey2]
     */
    public function setLink( \Nette\Application\UI\Link $link, $rowKeys = ['id'] ) {
        $this->link = $link;
        $this->rowKeys = $rowKeys;
    }
    
    public function setLatte( $latte ) {
        $this->latte = $latte;
    }
    
    public function setIcon( $icon ) {
        $this->icon = $icon;
    }
    
    public function setLabel( $label ) {
        $this->label = $label;
    }
    
    public function setOnCreateLink( $callback ) {
        $this->onCreateLink[] = is_callable($callback) ? $callback : null;
    }
    
    public function render( $row, $key ) {
        $link = $this->link ? clone $this->link: null;
        $template = $this->template;
        
        if(!$this->latte) $this->setLatte($this->getLattePath('link')); //default template for all
        
        $template->setFile($this->latte);
        $template->setTranslator($this->getTranslator());
        
        $template->class = implode(' ', $this->class);
        if (!$key) {
            $key = $this->rowKeys;
        }
        
        if (!empty($this->onCreateLink)) {
            $this->onCreateLink[0]($row, $link);
        } else {
            //If callback exists
            if(!$link && count($this->onAction)) {
                $link = $this->lazyLink('onAction');
            }
            
            if (is_array($key)) {
                foreach ($key as $rowKey => $linkKey) {
                    if (key_exists($rowKey, $row)) {
                        $link->setParameter($linkKey, $row[ $rowKey ]);
                    }
                }
            } else {
                $link->setParameter($this->rowKeys, $row[ $key ]);
            }
        }
        
        $template->icon = $this->icon;
        $template->link = $link;
        $template->label = $this->label;
        $template->confirm = $this->confirm;
        $template->render();
    }
    
    public function handleOnAction(){
        $rk = is_array($this->rowKeys)? $this->rowKeys : [$this->rowKeys];
        $params = [];
        
        foreach ($rk as $key){
            $params[$key] = $this->getParameter($key);
        }
        
        
        foreach ($this->onAction as $onAction){
            call_user_func($onAction, $params);
        }
    }
    
    public static function createAction( $action,  $link, $rowKeys = ['id'] ) {
        $actionObj = new Action;
        if(is_callable($link)) {
            $actionObj->onAction[] = $link;
            $actionObj->rowKeys = $rowKeys;
        } else {
            $actionObj->setLink($link, $rowKeys);
        }
        
        if (is_array($action)) {
            $actionObj->setCustomAction($action);
        } else {
            $actionObj->setAction($action);
        }
        
        return $actionObj;
    }
    
}