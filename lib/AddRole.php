<?php

namespace techsterx\SlimOpensrs\API;

class AddRole extends BaseClass
{
	public static function validate($data)
	{
		$roles = array('company', 'domain', 'mail', 'workgroup');

		if (empty($data['role']) || !in_array($data['role'], $roles)) {
			trigger_error("oSRS Error - No role found\n", E_USER_WARNING);
			return false;
		}
		
		if (empty($data['user']) || empty($data['object'])) {
			trigger_error("oSRS Error - User/Role/Object required\n", E_USER_WARNING);
			return false;
		}

		return true;
	}
}
