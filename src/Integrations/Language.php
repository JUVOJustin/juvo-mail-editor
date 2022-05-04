<?php

namespace JUVO_MailEditor\Integrations;

use WP_User;

class Language
{

	/**
	 * @param string $locale
	 * @param WP_User $user
	 * @return string either user locale if set or blog info locale
	 */
	public function getUserLocale(string $locale, WP_User $user): string {

		$userLocale = get_user_locale( $user );
		if (empty($userLocale)) {
			return get_bloginfo("language");
		}

		return $userLocale;
	}

}
