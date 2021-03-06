<?php
namespace Nuttilea\TableControl;
use Dibi\Connection;
use Dibi\Fluent;
use Nette\SmartObject;
use Nuttilea\EntityMapper\Repository;


/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 4.2.17
 * Time: 18:52
 */
class EntityMapperConnector implements ITableConnector {
    
    use SmartObject;
    
    protected $dibi;
    /** @var  \Dibi\Fluent */
    protected $selection;
    /** @var Repository $repository */
    protected $repository;

    protected $callback;
    protected $data;

    protected $where = [];

    public function __construct($dibi, Repository $repository, $where = []) {
        $this->repository = $repository;
        if ($where) $this->where = $where;
        if($dibi instanceof Connection) $this->dibi = $dibi;
        if($dibi instanceof Fluent) $this->selection = $dibi;
    }

    public function setOnFindAll(callable $callable){
        $this->callback = $callable;
    }

    public function findAll($columns = '*', $where = [], $orderBy = null, $order = 'ASC', $limit = null, $offset = null) {
        if($this->where) {
            $where = array_merge($this->where, $where);
        }

        $selection = $this->getSelection();
        if (!$selection) {
            $selection = $this->dibi->select('*')
                ->from($this->repository->getTableName());
        }
        if ($where) $selection->where($where);
        if ($orderBy) $selection->orderBy($orderBy, $order);
        if ($limit) $selection->limit($limit);
        if ($offset) $selection->offset($offset);
        $this->data = $selection->fetchAll();
        if (is_callable($this->callback)) $this->data = call_user_func($this->callback, array_map(function($i){return $i->toArray();}, $this->data));
        return  $this->repository->createEntities($this->data);
    }


    public function deleteItem($where = []) {
        if (!$where) return null;
        return $this->dibi->delete($this->repository->getTableName())
            ->where($where);
    }


    public function setDefaultSelection(Fluent $selection = null) {
        if (!$selection instanceof Fluent) {
            throw new \Exception('selection must be instance of DibiFluent');
        }

        $this->selection = $selection;
    }

    public function selectModifier($name, $value) {
        return $value === 0 ? array($name.' IS NULL') : array($name.'=%s', $value);
    }


    public function textModifier($name, $value) {
        return array($name.' LIKE %like~', $value);
    }


    public function numberModifier($name, $value) {
        $first = $m = ' %like~';
        if (\Nette\Utils\Strings::match($value, '~<=~')) {
            $m = '<=';
            $value = \Nette\Utils\Strings::replace($value, '~<=~');
        } else if (\Nette\Utils\Strings::match($value, '~>=~')) {
            $m = '>=';
            $value = \Nette\Utils\Strings::replace($value, '~>=~');
        } else if (\Nette\Utils\Strings::match($value, '~>~')) {
            $m = '>';
            $value = \Nette\Utils\Strings::replace($value, '~>~');
        } else if (\Nette\Utils\Strings::match($value, '~<~')) {
            $m = '<';
            $value = \Nette\Utils\Strings::replace($value, '~<~');
        }
        if ($m == $first){
            $key = $name.' LIKE '.$m;
        } else {
            $key = $name.$m.'%i';
        }

        return array($key, $value);
    }


    public function itemsCount($where = []) {

        $selection = $this->getSelection();
        if($this->where) {
            $where = array_merge($this->where, $where);
        }
        if ($where) $selection->where($where);

        $sel = $this->getSelection()->removeClause('ORDER BY')->removeClause('SELECT')->select('COUNT(*) as count')->where($where);

        $count = $sel->fetch();

        return $count ? (is_int($count) ? $count : (int) $count->count) : 0;
    }


    /**
     * @return \Dibi\Fluent
     */
    public function getSelection() {


        if (!$this->selection) {
            $this->selection = $this->dibi->select('*')
                ->from($this->repository->getTableName());
        }

        return clone $this->selection;
    }


    public function update($values, $where) {
        if(!$this->repository->getTableName()) {
            throw new Exception('Set table before. Not working with JOIN! Set own update method.');
        }
        return $this->dibi->update($this->repository->getTableName(), $values)->where($where)->execute();
    }
}
