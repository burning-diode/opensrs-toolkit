<?php

namespace techsterx\SlimOpensrs\API;

abstract class BaseClass
{
	protected static $allowBadCerts;
	protected static $apiHost;
	protected static $apiPort;

	protected static $responseHeaders;
	protected static $responseStatus;

	protected static $curlHeaders;

	abstract public static function validate($data);

	public function __construct(array $options = array())
	{
		self::$apiHost = $options['host'];
		self::$apiPort = $options['port'];

		self::$responseHeaders = array();
		self::$responseStatus = '';

		self::$curlHeaders = array();
	}

	public static function call($data)
	{
		if (self::validate($data)) {
			return self::call(self::decamelize(get_called_class(), $data));
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

	private static function makeCall($method, $request)
	{
		$data = array_merge(array(
			'credentials' => array(
				'user' => self::$username,
				'password' => self::$password,
				'client' => 'SlimOpenSRS API v1',
			),
		), $request);

		// We were passed an authentication token, don't send our password
		if (isset($request['credentials']['token'])) {
			unset($data['credentials']['password']);
		}

		$data_string = json_encode($data);

		$ch = curl_init(self::$apiHost . '/api/' . $method);

		// When in test/dev mode, don't verify the SSL certs
		if (strtolower(self::$env !== 'live') || self::$allowBadCerts) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, self::$curlHeaders);

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			throw new \Exception('Curl error: ' . $curl_error($curl), curl_errno($curl));
		}

		list($headers, $body) = explode("\r\n\r\n", $response, 2);
		$headers = explode("\r\n", $headers);

		foreach ($headers as $header) {
			list($key, $value) = explode(':', $header);

			if ($value === null || $value === '') {
				continue;
			}

			self::$responseHeaders[trim($key)] = trim($value);
		}

		self::$responseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return $body;
	}

	// Converts CamelCase into snake_case
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
}
