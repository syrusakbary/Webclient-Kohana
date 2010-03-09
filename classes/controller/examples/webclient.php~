<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Webclient extends Controller {

	public function action_index()
	{
		$navigator = Webclient::factory();
		$output = $navigator->url('http://www.google.es/search')
			->get(array('q'=>'uno'))
			->exec();
		$this->request->response = Kohana::debug($output['response']);
	}

} // End Welcome
