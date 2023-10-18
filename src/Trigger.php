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

	// Hold the class instance.
	private static $instance = null;

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

	private array $context = [];

	private string $mailHook;

	/**
	 * Trigger constructor.
	 *
	 * @param string $name
	 * @param string $slug
	 * @param string $mailHook
	 */
	public function __construct( string $name, string $slug, string $mailHook = '' ) {
		$this->name     = $name;
		$this->slug     = $slug;
		$this->mailHook = $mailHook;
		$this->term     = get_term_by( 'slug', $slug, Mail_Trigger_TAX::TAXONOMY_NAME ) ?: null;

		add_filter( $this->mailHook, array( $this, 'addTriggerToHeader' ), 9, 1 );
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

	public function getRelatedPosts(): array {

		if ( ! $this->getTerm() instanceof WP_Term ) {
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

	public function getMailHook(): string {
		return $this->mailHook;
	}

	public function getContext(): array {
		return $this->context;
	}

	public function setContext( array $context ): void {
		$this->context = $context;
	}

	/**
	 * Add trigger slug to mail header to identify throughout the process
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function addTriggerToHeader( array $args ): array {

		// Enforce headers to be array
		if ( ! empty( $args['headers'] ) && is_string( $args['headers'] ) ) {
			$args['headers'] = explode( ',', $args['headers'] );
		}

		$args['headers'][] = "X-JUVO-ME-Trigger: {$this->getSlug()}";

		return $args;

	}

}
