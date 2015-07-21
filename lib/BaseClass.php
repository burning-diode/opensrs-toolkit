<?php

namespace BurningDiode\OpenSRS\API;

abstract class BaseClass implements ValidatorInterface
{
	protected static $allowBadCerts;
	protected static $apiHost;
	protected static $apiPort;

	protected static $responseHeaders= array();
	protected static $responseStatus = 0;

	protected static $curlHeaders = array();

	protected static $env = 'live';

	public static function call($data)
	{
		self::$apiHost = 'https://admin.hostedemail.com';

		if (static::validate($data)) {
			return self::makeCall($data);
		}
	}

	public function getHeaders()
	{
		return self::$responseHeaders;
	}

	public function getStatus()
	{
		return self::$responseStatus;
	}

	public function addHeader($header)
	{
		if (is_array($header)) {
			$header = join(': ', $header);
		}

		array_push(self::$curlHeaders, $header);

		return $this;
	}

	private static function makeCall($request)
	{
		$method = self::getMethodName();

		$request['credentials']['client'] = 'Burning Diode OpenSRS Toolkit';

		// We were passed an authentication token, don't send our password
		if (isset($request['credentials']['token'])) {
			unset($request['credentials']['password']);
		}

		$data_string = json_encode($request);

		$ch = curl_init(self::$apiHost . '/api/' . $method);

		// When in test/dev mode, don't verify the SSL certs
		if (strtolower(self::$env !== 'live') || self::$allowBadCerts) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, self::$curlHeaders);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string),
		));

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			throw new \Exception('Curl error: ' . $curl_error($curl), curl_errno($curl));
		}

		list($headers, $body) = explode("\r\n\r\n", $response, 2);
		$headers = explode("\r\n", $headers);

		foreach ($headers as $header) {
			list($key, $value) = array_pad(explode(':', $header, 2), 2, null);

			if ($value === null || $value === '') {
				continue;
			}

			self::$responseHeaders[trim($key)] = trim($value);
		}

		self::$responseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return $body;
	}

	private static function getMethodName()
	{
		$class = explode('\\', get_called_class());
		return self::uncamelize(end($class));
	}

	// Converts CamelCase into snake_case
	private static function uncamelize($str)
	{
		$str = lcfirst($str);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		return preg_replace_callback('|([A-Z])|', $func, $str);
	}
	/*
	private function uncamelize($str)
	{
		$str = lcfirst($str);
		$lc = strtolower($str);
		$result = '';
		$length = strlen($str);

		for ($i = 0; $i < $length; ++$i) {
			$result .= ($str[$i] == $lc[$i] ? '' : '_') . $lc[$i];
		}

		return $result;
	}
	 */
}
