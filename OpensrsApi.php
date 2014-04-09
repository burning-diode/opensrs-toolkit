<?php

namespace techsterx\SlimOpensrs;

class OpensrsApi
{
	protected $app;

	public function __construct()
	{
		$this->app = \Slim\Slim::getInstance();
	}

	public function __call($name, $arguments)
	{
		//require_once dirname(__FILE__) . '/opensrs/openSRS_loader.php';

		$response = call_user_func_array(array(__NAMESPACE__.'\\API\\'.$class, 'call'), array($arguments));

		return json_decode($response);
	}

	private function mailSuperAdmin($field = null)
	{
		$config = $this->app->config('opensrs.authentication');
		$info = $config['mail'];

		return $field === null || !array_key_exists($field, $info) ? $info : $info[$field];
	}
}
