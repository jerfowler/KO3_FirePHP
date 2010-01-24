<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_FirePHP extends Controller {

  public function after() {
    FirePHP_Profiler::instance()
      ->post()
      ->session()
      ->database()
      ->benchmark();
    parent::after();
  }

}

