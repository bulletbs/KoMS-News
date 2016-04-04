<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Виджет "Меню админа"
 */
class Controller_Widgets_MostNews extends Controller_System_Widgets {

    public $template = 'global/widget';    // Шаблон виждета

    public function action_index()
    {
        $this->template = Request::factory('news/most')->execute();
    }

}