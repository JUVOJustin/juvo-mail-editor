<?php


namespace JUVO_MailEditor;

use WP_Term;

/**
 * Transportobject for mail trigger terms
 *
 * Class Trigger
 *
 * @package JUVO_MailEditor
 */
class Trigger {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $slug;

	/**
	 * @var WP_Term|null
	 */
	private ?WP_Term $term;

	/**
	 * Trigger constructor.
	 *
	 * @param string $name
	 * @param string $slug
	 */
	public function __construct( string $name, string $slug ) {
		$this->name = $name;
		$this->slug = $slug;
		$this->term = get_term_by( 'slug', $slug, Mail_Trigger_TAX::TAXONOMY_NAME ) ?: null;
	}

	public function getTerm(): ?WP_Term {
		return $this->term ?? null;
	}

	/**
	 * @return string
	 */
	public function getSlug(): string {
		return $this->slug;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Check if a trigger is globally muted
	 *
	 * @return bool
	 */
	public function isMuted(): bool {
		$pluginSettings = get_option( 'settings' );

		if ( isset( $pluginSettings['trigger_mute'] ) ) {
			return in_array( $this->slug, $pluginSettings['trigger_mute'], true );
		}

		return false;
	}

	public function getRelatedPosts() :array {

		if (!$this->getTerm() instanceof WP_Term) {
			return [];
		}

		return get_posts(
			array(
				'post_type'        => Mails_PT::POST_TYPE_NAME,
				'post_status'      => 'publish',
				'numberposts'      => - 1,
				'suppress_filters' => false,
				'tax_query'        => array(
					array(
						'taxonomy' => Mail_Trigger_TAX::TAXONOMY_NAME,
						'field'    => 'id',
						'terms'    => $this->term,
					),
				),
			)
		);
	}

}
