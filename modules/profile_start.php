<?php
// ############################################################################
// ############################### EDIT PASSWORD ##############################
// ############################################################################

if ($_REQUEST['do'] == 'twofactor')
{
	// draw cp nav bar
	construct_usercp_nav('twofactor');

	$page_templater = vB_Template::create('twofactor_setup');
	$page_templater->register('enabled', $vbulletin->userinfo['twofactor_enabled']);
}
