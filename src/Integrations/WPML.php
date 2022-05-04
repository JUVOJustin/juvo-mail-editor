<?php

namespace JUVO_MailEditor\Integrations;

use WP_User;

class WPML
{

	public function getUserLocale(string $locale, WP_User $user): string  {
		$languages = apply_filters( 'wpml_active_languages', NULL, ['skip_missing' => 1] );
		$lang = get_user_meta( $user->ID, 'icl_admin_language', true );

		if (!$lang || empty($languages)) {
			return $locale;
		}

		// Map stored user language to wpml configured languages
		foreach( $languages as $language ) {

			if ($language['language_code'] == $lang) {
				$locale = $language['default_locale'];
			}
		}

		return $locale;
	}

}
