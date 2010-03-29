<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Examples_Webclient extends Controller {

	public function action_index()
	{
		$navigator = Webclient::factory();
		$output = $navigator->url('http://www.youtube.com/')
			->cookieFile(APPPATH.'cache/file.txt')
			->get(array('q'=>'uno'))
			->execute();
		$this->request->response = Kohana::debug($output);
	}

} // End Welcome
