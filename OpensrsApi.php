<?php

namespace BurningDiode\OpenSRS;

class OpensrsApi
{
	protected $credentials;

	public function __construct()
	{
		$this->credentials = array(
			'user' => null,
			'password' => null,
		);
	}

	public function __set($name, $value)
	{
		switch ($name) {
			case 'user':
			case 'password':
				if (is_string($name)) {
					$this->credentials[$name] = $value;
				} else {
					$this->trigger_error('Invalid type for property ' . $name);
				}
				break;
			default:
				$this->trigger_error('Undefined property ' . $name);
				break;
		}
	}

	public function __get($name)
	{
		if (array_key_exists($name, $this->credentials)) {
			return $this->credentials[$name];
		}

		$this->trigger_error('Undefined property ' . $name);

		return null;
	}

	public function __call($name, $data)
	{
		$arguments = array_merge(array(
			'credentials' => $this->credentials,
		), $data[0]);

		$response = call_user_func_array(array(__NAMESPACE__ . '\\API\\' . $name, 'call'), array($arguments));

		return is_array($response) ? $response : json_decode($response);
	}

	final private function trigger_error($msg, $level = E_USER_NOTICE)
	{
		$trace = debug_backtrace();

		trigger_error($msg . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], $level);
	}
}
