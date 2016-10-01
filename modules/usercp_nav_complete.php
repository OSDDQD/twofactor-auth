<?php
$templater = vB_Template::create('dbtech_twofactor_usercp_settings_link');
	$templater->register('navclass', $navclass);
$template_hook['usercp_navbar_myaccount_list'] .= $templater->render();
?>