<?php
if (in_array('usercp_nav_folderbit', (array) $cache) OR in_array('usercp_nav_folderbit', (array) $globaltemplates))
{
	$cache[] = 'twofactor_usercp_settings_link';
}

if (THIS_SCRIPT == 'profile' AND $_REQUEST['do'] == 'twofactor')
{
	$cache[] = 'twofactor_setup';
}

if (THIS_SCRIPT == 'misc')
{
	$cache[] = 'twofactor_enable';
	$cache[] = 'twofactor_disable';
	$cache[] = 'twofactor_verifycode';
	$cache[] = 'GENERIC_SHELL';
}
