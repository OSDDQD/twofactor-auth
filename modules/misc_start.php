<?php
// ############################### start buddylist ###############################
if ($_REQUEST['do'] == 'twofactor')
{
	switch ($_REQUEST['action'])
	{
		case 'enable':
			if ($vbulletin->userinfo['twofactor_auth_enabled'])
			{
				// Throw error from invalid action
				eval(standard_error(fetch_error('twofactor_already_enabled')));
			}

			// Set page title
			$pagetitle = $vbphrase['twofactor_enable'];

			if (!class_exists('vB_Mail', false))
			{
				require_once(DIR . '/includes/class_mail.php');
			}

			$mail = vB_Mail::fetchLibrary($vbulletin);
			$mail->start($vbulletin->userinfo['email'], $subject, $message, $vbulletin->options['webmasteremail']);

			// Grab our template
			$templater = vB_Template::create('twofactor_enable');
				$templater->register('pagetitle', $pagetitle);
			$HTML = $templater->render();
			break;

		case 'doenable':
			if ($vbulletin->userinfo['twofactor_auth_enabled'])
			{
				// Throw error from invalid action
				eval(standard_error(fetch_error('twofactor_already_enabled')));
			}

			// Grab our vars
			$vbulletin->input->clean_array_gpc('p', array(
				'code' 		=> TYPE_NOHTML,
			));

			// We can trust this IP now
			$db->query_write("
				INSERT IGNORE INTO " . TABLE_PREFIX . "twofactor_ipverify
					(userid, sessionhash, authorised)
				VALUES (
					'" . intval($vbulletin->userinfo['userid']) . "',
					'" . $vbulletin->session->vars['dbsessionhash'] . "',
					'1'
				)
				ON DUPLICATE KEY UPDATE authorised = '1'
			");

			$vbulletin->url = fetch_seo_url('forumhome', array());
			print_standard_redirect('twofactor_enabled_success', true, true);
			break;

		case 'disable':
			if (!$vbulletin->userinfo['dbtech_twofactor_enabled'])
			{
				// Throw error from invalid action
				eval(standard_error(fetch_error('dbtech_twofactor_not_enabled')));
			}

			// Set page title
			$pagetitle = $vbphrase['dbtech_twofactor_disable'];

			// Grab our template
			$templater = vB_Template::create('dbtech_twofactor_disable');
				$templater->register('pagetitle', 	$pagetitle);
			$HTML = $templater->render();
			break;

		case 'dodisable':
			if (!$vbulletin->userinfo['twofactor_auth_enabled'])
			{
				// Throw error from invalid action
				eval(standard_error(fetch_error('dbtech_twofactor_not_enabled')));
			}

			// Grab our vars
			$vbulletin->input->clean_gpc('p', 'code', TYPE_NOHTML);

			if ($vbulletin->GPC['code'] != $vbulletin->userinfo['dbtech_twofactor_recovery'])
			{
				if (!$ga->verifyCode($vbulletin->userinfo['dbtech_twofactor_secret'], $vbulletin->GPC['code'], 2))
				{
					// Throw error from invalid action
					eval(standard_error(fetch_error('dbtech_twofactor_invalid_code')));
				}
			}

			// Remove IP auths
			$db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_twofactor_ipverify WHERE userid = '" . intval($vbulletin->userinfo['userid']) . "'");
			$db->query_write("UPDATE " . TABLE_PREFIX . "user SET dbtech_twofactor_secret = '', dbtech_twofactor_recovery = '' WHERE userid = '" . intval($vbulletin->userinfo['userid']) . "'");

			$vbulletin->url = fetch_seo_url('forumhome', array());
			print_standard_redirect('dbtech_twofactor_disabled_success', true, true);
			break;

		case 'doverify':
			if (!$vbulletin->userinfo['dbtech_twofactor_enabled'])
			{
				// Throw error from invalid action
				eval(standard_error(fetch_error('dbtech_twofactor_not_enabled')));
			}

			// Grab our vars
			$vbulletin->input->clean_gpc('p', 'code', TYPE_NOHTML);

			if (!$ga->verifyCode($vbulletin->userinfo['dbtech_twofactor_secret'], $vbulletin->GPC['code'], 2))
			{
				// Throw error from invalid action
				eval(standard_error(fetch_error('dbtech_twofactor_invalid_code')));
			}

			// We can trust this IP now
			$db->query_write("
				INSERT IGNORE INTO " . TABLE_PREFIX . "dbtech_twofactor_ipverify
					(userid, ipaddress, authorised)
				VALUES (
					'" . intval($vbulletin->userinfo['userid']) . "',
					'" . IPADDRESS . "',
					'1'
				)
				ON DUPLICATE KEY UPDATE authorised = '1'
			");

			$vbulletin->url = fetch_seo_url('forumhome', array());
			print_standard_redirect('dbtech_twofactor_verified_success', true, true);
			break;

		default:
			if (!$vbulletin->userinfo['dbtech_twofactor_enabled'])
			{
				// Throw error from invalid action
				eval(standard_error(fetch_error('dbtech_twofactor_not_enabled')));
			}

			// Set page title
			$pagetitle = $vbphrase['dbtech_twofactor_verifycode'];

			// Grab our template
			$templater = vB_Template::create('dbtech_twofactor_verifycode');
				$templater->register('pagetitle', 	$pagetitle);
			$HTML = $templater->render();
			break;
	}

	$navbits = construct_navbits(array(
		'usercp.php' . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['user_control_panel'],
		'profile.php?' . $vbulletin->session->vars['sessionurl'] . 'do=twofactor' => $vbphrase['dbtech_twofactor_twofactor'],
		'' => $pagetitle
	));
	$navbar = render_navbar_template($navbits);

	$templater = vB_Template::create('GENERIC_SHELL');
		$templater->register_page_templates();
		$templater->register('navbar', 		$navbar);
		$templater->register('HTML', 		$HTML);
		$templater->register('pagetitle', 	$pagetitle);
	print_output($templater->render());
}
?>