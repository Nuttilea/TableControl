<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 5.2.17
 * Time: 19:13
 */
namespace Nuttilea\TableControl;
interface IFilterAdd{
    public function onFilterAdded(TableColumn $column);
}