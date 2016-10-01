<?php
global $vbulletin, $show;

if (
	$vbulletin->userinfo['userid']
	AND $vbulletin->userinfo['twofactor_auth_enabled']
)
{
	// Two-Factor Auth enabled
	if (!$ipverify = $vbulletin->db->query_first_slave("
		SELECT authorised FROM " . TABLE_PREFIX . "twofactor_verify
		WHERE userid = '" . intval($vbulletin->userinfo['userid']) . "'
			AND sessionhash = '" . $vbulletin->session->vars['dbsessionhash'] . "'
	"))
	{
		// IP address doesn't exist for that user
		$vbulletin->db->query_write("
			INSERT IGNORE INTO " . TABLE_PREFIX . "twofactor_verify
				(userid, sessionhash, authorised)
			VALUES (
				'" . intval($vbulletin->userinfo['userid']) . "',
				'" . $vbulletin->session->vars['dbsessionhash'] . "',
				'0'
			)
		");

		// Default values
		$ipverify = array('authorised' => 0);
	}

	if (
		!$vbulletin->session->vars['profileupdate'] AND
		!($vbulletin->userinfo['password'] == md5(md5($vbulletin->userinfo['username']) . $vbulletin->userinfo['salt'])) AND
		!$show['passwordexpired'] AND
		!$ipverify['authorised'] AND (
			THIS_SCRIPT != 'misc' OR (
				THIS_SCRIPT == 'misc' AND
				$_REQUEST['do'] != 'twofactor'
			)
		)
	)
	{
		// Redirect to the dedicated file
		exec_header_redirect((VB_AREA != 'Forum' ? '../' : '') . 'misc.php?' . $vbulletin->session->vars['sessionurl'] . 'do=twofactor');
	}
}
?>