<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_FirePHP extends Controller {

  public function after() {
	FirePHP_Profiler::instance()
		->group('KO3 FirePHP Application Profiler')
		->post()
		->get()
		->session()
		->cookie()
		->database()
		->benchmark()
		->groupEnd();
    parent::after();
  }

}

