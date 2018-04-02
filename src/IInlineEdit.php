<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 31.3.17
 * Time: 13:09
 */

namespace Nutillea\TableView;

interface IInlineEdit {
    public function handleInlineEdit($id);
}