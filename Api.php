<?php

namespace BurningDiode\OpenSRS;

/**
 * @property string $user
 * @property string $password
 * @property-read array $headers
 * @property-read string $payload
 * @property-read int $status
 * @property-read int $errno
 * @property-read string $errmsg
 * @property-read string $audit
 * @property-read bool $success
 */
class Api
{
	/**
	 * @var array $credentials
	 */
	protected $credentials;

	/**
	 * @var array $headers
	 */
	protected $headers;

	/**
	 * @var string|null $payload
	 */
	protected $payload;

	/**
	 * @var int $errno
	 */
	protected $errno;

	/**
	 * @var int $status
	 */
	protected $status;

	/**
	 * @var string|null $errmsg
	 */
	protected $errmsg;

	/**
	 * @var string|null $audit
	 */
	protected $audit;

	/**
	 * @var bool $success
	 */
	protected $success;

	public function __construct()
	{
		$this->init();

		$this->credentials = array(
			'user' => null,
			'password' => null,
		);
	}

	/**
	 * Initializes all properties back to defaults;
	 *
	 * @return void
	 */
	final private function init()
	{
		$this->headers = array();
		$this->payload = null;
		$this->errno = 0;
		$this->status = 0;
		$this->errmsg = null;
		$this->audit = null;
		$this->success = false;
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

	public function __get($property)
	{
		switch ($property) {
			case 'success':
				return (bool) $this->$property;
			case 'errno':
			case 'status':
				return (int) $this->$property;
			case 'errmsg':
			case 'headers':
			case 'audit':
			case 'payload':
				return $this->$property;
		}
		
		if (array_key_exists($property, $this->credentials)) {
			return $this->credentials[$property];
		}

		$this->trigger_error('Undefined property ' . $property);

		return null;
	}

	public function __call($name, $data)
	{
		$this->init();

		$arguments = array_merge(array(
			'credentials' => $this->credentials,
		), $data[0]);

		$response = call_user_func_array(array(__NAMESPACE__ . '\\API\\' . $name, 'call'), array($arguments));

		return is_array($response) ? $response : json_decode($response);
	}

	final public function call($name, $data)
	{
		$this->init();

		$arguments = array_merge(array(
			'credentials' => $this->credentials,
		), $data);

		$name = str_replace('_', '', mb_convert_case($name, MB_CASE_TITLE));

		$classname =  __NAMESPACE__ . '\\API\\' . $name;
		$instance  = new $classname();

		$response = $instance->call($arguments);
		$payload = is_array($reponse) || is_object($response) ? $response : json_decode($response);

		$this->status = (int) $instance->getStatus();
		$this->headers = $instance->getHeaders();

		$this->success = (bool) $payload->success;
		$this->audit = $payload->audit;

		if ($this->success === false) {
			$this->errmsg = $payload->error;
			$this->errno = $payload->error_number;

			unset($payload->error);
			unset($payload->error_number);
		}

		unset($payload->success);
		unset($payload->audit);

		$this->payload = $payload;

		return $this->success;
	}

	final private function trigger_error($msg, $level = E_USER_NOTICE)
	{
		$trace = debug_backtrace();

		trigger_error($msg . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], $level);
	}
}
