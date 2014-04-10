<?php

namespace techsterx\SlimOpensrs\API;

class SearchUsers extends \techsterx\SlimOpensrs\API\BaseClass
{
	public static function validate($data)
	{
		if (empty($data['domain'])) {
			trigger_error("oSRS Error - Domain required\n", E_USER_WARNING);
		} else {
			return true;
		}
	}
}
