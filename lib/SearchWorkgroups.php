<?php

namespace BurningDiode\OpenSRS\API;

class SearchWorkgroups extends BaseClass
{
	public static function validate($data)
	{
		if (empty($data['criteria']['domain'])) {
			trigger_error("oSRS Error - Domain required\n", E_USER_WARNING);
		} else {
			return true;
		}
	}
}
