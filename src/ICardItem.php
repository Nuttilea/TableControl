<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 7/12/18
 * Time: 6:57 PM
 */

interface ICardItem {
    public function getTitle();
    public function getActions();
    public function getDescriptions();
    public function getImage();
}