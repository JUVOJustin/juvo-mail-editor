<?php


namespace JUVO_MailEditor;


use JUVO_MailEditor\Mails\Generic;
use WP_Post;
use WP_Term;
use WP_User;

class Relay {

	/**
	 * @var string
	 */
	private $trigger;

	/**
	 * @var array
	 */
	private $placeholders;

	/**
	 * @var WP_Term
	 */
	private $term;

	/**
	 * @var int[]|WP_Post[]
	 */
	private $posts;

	private $context;

	/**
	 * Relay constructor.
	 *
	 * @param string $trigger
	 * @param array $placeholders
	 * @param mixed $context
	 */
	public function __construct( string $trigger, array $placeholders, $context = null ) {
		$this->trigger      = $trigger;
		$this->context      = $context;
		$this->term         = $this->setTerm();
		$this->posts        = $this->setPostsForTrigger();
		$this->placeholders = $this->preparePlaceholders( $placeholders );
	}

	/**
	 * Sends mails for all posts that are associated with the trigger.
	 * To send mails even if not post is associated set the "alwaysSend" flag.
	 *
	 * If the flag is set the term defaults are used for the mailing
	 */
	public function sendMails() {

		// Add Muted Capability
		if ( self::triggerIsMuted( $this->trigger ) ) {
			return false;
		}

		if ( ! empty( $this->posts ) ) {
			foreach ( $this->posts as $post ) {

				// Content
				$content = $this->prepareContent( $post );

				// Subject
				$subject = $this->prepareSubject( $post );

				// Recipients
				$recipients = $this->prepareRecipients( $post );

				$mail = new Generic( $subject, $content, $recipients );
				$mail->send();

			}
		} else {

			// Some triggers might only send mails if a post is associated
			$alwaysSent = get_term_meta( $this->term->term_id, Mail_Trigger_TAX::TAXONOMY_NAME . "_always_send", true );
			if ( ! $alwaysSent ) {
				return false;
			}

			/// Fallback if not posts are found for configured trigger
			$content    = $this->prepareContent();
			$subject    = $this->prepareSubject();
			$recipients = $this->prepareRecipients();

			$mail = new Generic( $subject, $content, $recipients );
			$mail->send();
		}

		return true;
	}

	/**
	 * @return array
	 */
	public function getPlaceholders(): array {
		return $this->placeholders;
	}

	/**
	 * @return WP_Term
	 */
	public function getTerm() {
		return $this->term;
	}

	/**
	 * @return WP_Post[]
	 */
	public function getPosts(): array {
		return $this->posts;
	}

	/**
	 * @return WP_Post[]
	 */
	private function setPostsForTrigger(): array {

		return get_posts( [
			'post_type'   => Mails_PT::POST_TYPE_NAME,
			'post_status' => 'publish',
			'numberposts' => - 1,
			'tax_query'   => [
				[
					'taxonomy' => Mail_Trigger_TAX::TAXONOMY_NAME,
					'field'    => 'id',
					'terms'    => $this->term
				]
			]
		] );

	}

	/**
	 * @return false|WP_Term
	 */
	private function setTerm() {
		return get_term_by( "slug", $this->trigger, Mail_Trigger_TAX::TAXONOMY_NAME );
	}

	/**
	 * @param WP_Post|null $post
	 *
	 * @return string
	 */
	public function prepareContent( WP_Post $post = null ): string {

		if ( ! $post || ! $content = $post->post_content ) {
			$content = get_term_meta( $this->term->term_id, Mail_Trigger_TAX::TAXONOMY_NAME . "_default_content", true );
		} else {
			$blocks  = parse_blocks( $content );
			$content = '';

			foreach ( $blocks as $block ) {
				$content .= render_block( $block );
			}
		}

		$content = apply_filters( "juvo_mail_editor_before_content_placeholder", $content, $this->trigger, $this->context );
		$content = Placeholder::replacePlaceholder( $this->placeholders, $content, $this->context );
		$content = apply_filters( "juvo_mail_editor_after_content_placeholder", $content, $this->trigger, $this->context );

		$this->setContentType( $content );

		return $content;
	}

	/**
	 * @param WP_Post|null $post
	 *
	 * @return string
	 */
	public function prepareSubject( WP_Post $post = null ): string {

		if ( ! $post || ! $subject = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_subject', true ) ) {
			$subject = get_term_meta( $this->term->term_id, Mail_Trigger_TAX::TAXONOMY_NAME . "_default_subject", true );
		}

		$subject = apply_filters( "juvo_mail_editor_before_subject_placeholder", $subject, $this->trigger, $this->context );
		$subject = Placeholder::replacePlaceholder( $this->placeholders, $subject, $this->context );

		return apply_filters( "juvo_mail_editor_after_subject_placeholder", $subject, $this->trigger, $this->context );
	}

	/**
	 * @param WP_Post|null $post
	 *
	 * @return string
	 */
	public function prepareRecipients( WP_Post $post = null ): string {

		if ( ! $post || ! $recipients = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_recipients', true ) ) {
			$recipients = get_term_meta( $this->term->term_id, Mail_Trigger_TAX::TAXONOMY_NAME . "_default_recipients", true );
		}

		$recipients   = apply_filters( "juvo_mail_editor_before_recipient_placeholder", $recipients, $this->trigger, $this->context );
		$placeholders = $this->placeholders;
		if ( $this->context instanceof WP_User ) {
			$placeholders["context"] = $this->context->user_email;
		}
		$recipients = Placeholder::replacePlaceholder( $placeholders, $recipients, $this->context );

		return apply_filters( "juvo_mail_editor_after_recipient_placeholder", $recipients, $this->trigger, $this->context );
	}

	/**
	 * @param array $placeholders
	 *
	 * @return array
	 */
	public function preparePlaceholders( array $placeholders ): array {
		$defaultPlaceholders = get_term_meta( $this->term->term_id, Mail_Trigger_TAX::TAXONOMY_NAME . "_placeholders", true );

		if ( ! $defaultPlaceholders ) {
			return $defaultPlaceholders;
		}

		return $placeholders + $defaultPlaceholders;
	}

	public function setContentType( string $message ): string {

		$type = 'text/plain';

		if ( $message != strip_tags( $message ) ) {
			$type    = "text/html";
			$message = wpautop( $message );
		}

		add_filter( 'wp_mail_content_type', function( $content_type ) use ( $type ) {
			return $type;
		}, 10 );

		return $message;
	}

	/**
	 * Checks if the given trigger is globally muted.
	 * Might be called manually if the mail is not send using the 'Relay' class but eg. with a filter.
	 *
	 * @param string $trigger
	 *
	 * @return bool
	 */
	public static function triggerIsMuted( string $trigger ): bool {
		$pluginSettings = get_option( 'settings' );

		if ( isset( $pluginSettings['trigger_mute'] ) ) {
			$mutedTriggers = $pluginSettings['trigger_mute'];

			return in_array( $trigger, $mutedTriggers );
		}

		return false;
	}

}
