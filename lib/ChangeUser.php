<?php

namespace BurningDiode\OpenSRS\API;

class ChangeUser extends BaseClass
{
	public static function validate($data)
	{
		if (empty($data['user'])) {
			trigger_error("oSRS Error - User required\n", E_USER_WARNING);
		} else {
			return true;
		}
	}
}
