<?php

namespace JUVO_MailEditor;


use juvo\WordPressAdminNotices\Manager;

class Deactivator {

	public static function deactivate() {

		// Remove all Notices on Deactivation
		Manager::remove( "juvo_mail_editor_missing_plugin");

	}

}
