<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Template_FirePHP extends Controller_Template {

  public function after() {
    FirePHP_Profiler::instance()
      ->post()
      ->session()
      ->database()
      ->benchmark();
    parent::after();
  }

}

