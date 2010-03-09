<?php defined('SYSPATH') or die('No direct script access.');

class Webclient extends Syrus_Webclient {
}
/*Example
var_dump(
	Webclient::factory()
	->url('https://www.cia.gov/?primero=si')
	->get(array('p'=>'uno'))
	->post(array('p'=>'uno'))
	->exec()
);
*/
