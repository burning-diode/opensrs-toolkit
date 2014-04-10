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
		$arguments = array_merge(array('credentials' => $this->mailAuth()), $arguments[0]);

		$response = call_user_func_array(array(__NAMESPACE__.'\\API\\'.$name, 'call'), array($arguments));

		return is_array($response) ? $response : json_decode($response);
	}

	private function mailAuth()
	{
		$allowed_keys = array('user', 'password');

		$config = $this->app->config('opensrs.authentication');

		foreach ($config['mail'] as $key => $value) {
			if (!in_array($key, $allowed_keys)) {
				unset($config['mail'][$key]);
			}
		}

		return $config['mail'];
	}
}
