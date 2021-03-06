<?php defined('SYSPATH') or die('No direct script access.');

class Webclient_Core {
	private $curl;
	private $options;
	private $get = array();
	private $url = array();
	protected $default_options = array(
		//CURLOPT_SSL_VERIFYPEER => false,
		/*CURLOPT_POST => false,
		CURLOPT_POSTFIELDS => NULL,
		CURLOPT_COOKIEJAR => NULL,
		CURLOPT_COOKIEFILE => NULL,*/
		CURLOPT_RETURNTRANSFER => true
	);
	protected $default_options_main;

	public static function factory()
	{
		return new Webclient();
	}
	public function __construct() {
		$this->curl = curl_init();
		$this->restoreDefaultOptions();
	}

	public function buildUrl() {
		
		$parsed = $this->url;
		if (!is_array($parsed))
			return false;

		$uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '' : '//') : '';
		$uri .= isset($parsed['user']) ? $parsed['user'].(isset($parsed['pass']) ? ':'.$parsed['pass'] : '').'@' : '';
		$uri .= isset($parsed['host']) ? $parsed['host'] : '';
		$uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';

		if (isset($parsed['path'])) {
			$uri .= (substr($parsed['path'], 0, 1) == '/') ?
			$parsed['path'] : ((!empty($uri) ? '/' : '' ) . $parsed['path']);
		}
		
		$uri .= isset($parsed['query']) ? '?'.$parsed['query'] : '';

		return $uri;
	}
	public function restoreDefaultOptions () {
		$this->default_options_main = $this->default_options;
	}
	public function setOptions($key, $value)
	{
		$key = (defined($key) ? constant($key) : $key);
		$this->options[$key] = $value;
	}
	public function setDefault () {
		$this->default_options_main = $this->options;
	}
	public function __set($key, $value)
	{
		$this->setOptions($key, $value);
	}
	public function __get($key)
	{
		$key = (defined($key) ? constant($key) : $key);
		return $this->options[$key];
	}
	public function _url ($url=NULL) {
		$this->url = parse_url($url);
	}
	public function _user_agent ($user_agent='') {
		$this->CURLOPT_USERAGENT = $user_agent;
	}
	public function _ssl ($notssl=false) {
		$this->CURLOPT_SSL_VERIFYPEER = !$notssl;
	}
	private function fields ($array) {
		$fields = array();
		foreach ($array as $b => $c) $postfields[] = $b.'='.$c;
		return implode('&',$postfields);
	}
	public function _post ($data=NULL) {
		$this->CURLOPT_POST = isset($data);
		if (is_array($data)) {
			$this->CURLOPT_POSTFIELDS = $this->fields($data);
		}
	}
	public function _cookieFile ($file=false) {
		$this->CURLOPT_COOKIEJAR = $file;
		$this->CURLOPT_COOKIEFILE = $file;
	}
	public function _cookie ($cookie) {
		$this->CURLOPT_COOKIE = $cookie;
	}
	public function _get ($data=NULL) {
		$this->get += ($data);
		$this->url['query'] = (isset($this->url['query'])?$this->url['query'].'&':'').http_build_query($this->get);
	}
	public function custom ($options) {
		curl_setopt_array($this->curl,$options);
	}
	public function execute () {
		$this->CURLOPT_RETURNTRANSFER = true;
		$this->CURLOPT_URL = $this->buildUrl();
		$this->CURLINFO_HEADER_OUT = 1;
		$options = ($this->options+$this->default_options_main);
		$this->custom($options);
		$data = new stdClass;
		$data->response = curl_exec($this->curl);
		$data->info = curl_getinfo($this->curl);
		$data->error = curl_error($this->curl);
		$data->errno = curl_errno($this->curl);
		return $data;
	}
	public function close () {
		curl_close($this->curl);
	}
	public function __call ($name, $args) {
		call_user_func_array (array(&$this, '_'.$name), (array) $args );
		return $this;
	}
	public function status()
	{
		// Get the hostname and path
		$url = $this->url;

		if (empty($url['path']))
		{
			// Request the root document
			$url['path'] = '/';
		}

		// Open a remote connection
		$port = isset($url['port']) ? $url['port'] : 80;
		$remote = fsockopen($url['host'], $port, $errno, $errstr, 5);

		if ( ! is_resource($remote))
			return FALSE;

		// Set CRLF
		$CRLF = "\r\n";

		// Send request
		fwrite($remote, 'HEAD '.$url['path'].' HTTP/1.0'.$CRLF);
		fwrite($remote, 'Host: '.$url['host'].$CRLF);
		fwrite($remote, 'Connection: close'.$CRLF);

		// Send one more CRLF to terminate the headers
		fwrite($remote, $CRLF);

		// Remote is offline
		$response = FALSE;

		while ( ! feof($remote))
		{
			// Get the line
			$line = trim(fgets($remote, 512));

			if ($line !== '' AND preg_match('#^HTTP/1\.[01] (\d{3})#', $line, $matches))
			{
				// Response code found
				$response = (int) $matches[1];
				break;
			}
		}

		// Close the connection
		fclose($remote);

		return $response;
	}
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
