<?php

/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 3.2.17
 * Time: 20:40
 */
namespace Nuttilea\TableControl;

use Dibi\Fluent;

interface ITableConnector
{

    public function setTable($table);
    public function setDefaultSelection(Fluent $selection);
    public function findAll( $columns = '*', $where=[], $orderBy=null, $order='ASC', $limit = null, $offset=null);
    public function itemsCount( $where=[] );
    public function deleteItem( $where=[] );
    public function update($values, $where);
    /* Special filter modifiers (*, *a*, SQL REGEXP, > 5, < 900 ... ) */
    public function textModifier($name, $value);
    public function numberModifier($name, $value);
    public function selectModifier($name, $value);

}