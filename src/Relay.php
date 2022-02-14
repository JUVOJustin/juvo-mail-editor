<?php


namespace JUVO_MailEditor;

use JUVO_MailEditor\Mails\Generic;
use WP_Post;
use WP_Term;

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
	 * @param array $context
	 * @param WP_Term $term
	 */
	public function __construct( string $trigger, array $context, WP_Term $term ) {
		$this->trigger      = $trigger;
		$this->context      = $context;
		$this->term         = $term;
		$this->posts        = $this->setPostsForTrigger();
		$this->placeholders = $this->preparePlaceholders();
	}

	/**
	 * @return WP_Post[]
	 */
	private function setPostsForTrigger(): array {

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

	/**
	 * @return array
	 */
	public function preparePlaceholders(): array {
		return apply_filters( "juvo_mail_editor_{$this->trigger}_placeholders", array(), $this->context );
	}

	/**
	 * Sends mails for all posts that are associated with the trigger.
	 * To send mails even if not post is associated set the "alwaysSend" flag.
	 *
	 * If the flag is set the term defaults are used for the mailing
	 */
	public static function sendMails( string $trigger, array $context = array() ) {

		$term = get_term_by( 'slug', $trigger, Mail_Trigger_TAX::TAXONOMY_NAME );

		if ( ! $term instanceof WP_Term ) {
			return false;
		}

		// Add Muted Capability
		if ( self::triggerIsMuted( $trigger ) ) {
			return false;
		}

		$relay = new self( $trigger, $context, $term );

		// Store blog language defaults
		restore_previous_locale();
		$blogLocale = get_locale();
		$blogLang   = locale_get_primary_language( $blogLocale );

		if ( ! empty( $relay->posts ) ) {
			// If templates were created for trigger

			foreach ( $relay->posts as $post ) {

				$locale = apply_filters( "juvo_mail_editor_{$trigger}_language", $blogLocale, $relay->context );
				$lang   = locale_get_primary_language( $locale );

				// Switch language context
				$switched_locale = switch_to_locale( $locale );

				// Get translated post with WPML
				$translationId = apply_filters( 'wpml_object_id', $post->ID, Mails_PT::POST_TYPE_NAME, true, $lang );
				if ( $translationId && $translationId !== $post->ID && get_post_status( $translationId ) === 'publish' ) {
					$post = get_post( $translationId );
				}

				// Recipients
				$recipients = $relay->prepareRecipients( $post );

				// Content
				$content = $relay->prepareContent( $post );

				// Subject
				$subject = $relay->prepareSubject( $post );

				// Headers
				$headers = $relay->prepareHeaders( $post );

				// Attachments
				$attachments = $relay->prepareAttachments( $post );

				// Restore language context
				if ( $switched_locale ) {
					restore_previous_locale();
				}

				$mail = new Generic( $subject, $content, $recipients, $headers, $attachments );
				$mail->send();

			}
		} else {
			// No templates created use trigger defaults

			// Some triggers might only send mails if a post is associated
			$alwaysSent = apply_filters( "juvo_mail_editor_{$trigger}_always_sent", false, $relay->context );
			if ( ! $alwaysSent ) {
				return false;
			}

			$lang = apply_filters( "juvo_mail_editor_{$trigger}_language", $blogLocale, $relay->context );

			$switched_locale = switch_to_locale( $lang );

			// Fallback if not posts are found for configured trigger
			$content     = $relay->prepareContent();
			$subject     = $relay->prepareSubject();
			$recipients  = $relay->prepareRecipients();
			$headers     = $relay->prepareHeaders();
			$attachments = $relay->prepareAttachments();

			if ( $switched_locale ) {
				restore_previous_locale();
			}

			$mail = new Generic( $subject, $content, $recipients, $headers, $attachments );
			$mail->send();

		}

		return true;
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
			return in_array( $trigger, $pluginSettings['trigger_mute'], true );
		}

		return false;
	}

	/**
	 * @param WP_Post|null $post
	 *
	 * @return array
	 */
	public function prepareRecipients( WP_Post $post = null ): array {

		$recipients = [];

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post ) {
			$recipients = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_recipients', true ) ?: [];
		}

		$recipients = apply_filters( "juvo_mail_editor_{$this->trigger}_recipients", $recipients, $this->context );

		return apply_filters( 'juvo_mail_editor_after_recipients_placeholder', $this->parseToCcBcc( $recipients ), $this->trigger, $this->context );
	}

	/**
	 * @param WP_Post|null $post
	 *
	 * @return string
	 */
	public function prepareContent( WP_Post $post = null ): string {

		$content = '';

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post && $content = get_the_content( null, false, $post ) ) {
			$blocks  = parse_blocks( $content );
			$content = '';

			foreach ( $blocks as $block ) {
				$content .= render_block( $block );
			}
		}

		$content = apply_filters( "juvo_mail_editor_{$this->trigger}_message", $content, $this->context );
		$content = Placeholder::replacePlaceholder( $this->placeholders, $content, $this->context );
		$content = apply_filters( 'juvo_mail_editor_after_content_placeholder', $content, $this->trigger, $this->context );

		$content = $this->setContentType( $content );

		return $content;
	}

	public function setContentType( string $message ): string {

		$type    = 'text/html';
		$message = wpautop( $message );

		add_filter(
			'wp_mail_content_type',
			function( $content_type ) use ( $type ) {
				return $type;
			},
			10
		);

		return $message;
	}

	/**
	 * @param WP_Post|null $post
	 *
	 * @return string
	 */
	public function prepareSubject( WP_Post $post = null ): string {

		$subject = '';

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post ) {
			$subject = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_subject', true );
		}

		$subject = apply_filters( "juvo_mail_editor_{$this->trigger}_subject", $subject, $this->context );
		$subject = Placeholder::replacePlaceholder( $this->placeholders, $subject, $this->context );

		return apply_filters( 'juvo_mail_editor_after_subject_placeholder', $subject, $this->trigger, $this->context );
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
	 * Sets custom headers.
	 * By defaults adds headers to identify mail-editor mails through wordpress
	 *
	 * @param WP_Post|null $post
	 *
	 * @return mixed|void
	 */
	private function prepareHeaders( WP_Post $post = null ) {

		$headers[] = "X-JUVO-ME-Trigger: {$this->trigger}";

		if ( $post ) {
			$headers[] = "X-JUVO-ME-PostID: {$post->ID}";
		}

		// Add CC and BCC
		$headers = $this->prepareCc( $headers, $post );
		$headers = $this->prepareBcc( $headers, $post );

		return apply_filters( "juvo_mail_editor_{$this->trigger}_headers", $headers, $this->context );
	}

	private function prepareAttachments( WP_Post $post = null ): array {

		$attachments = [];

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post ) {
			$attachments = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_attachments', true ) ?: [];

			if ( empty( $attachments ) ) {
				return $attachments;
			}

			foreach ( $attachments as $id => &$attachment ) {
				$attachment = get_attached_file( $id );
			}
		}

		return apply_filters( "juvo_mail_editor_{$this->trigger}_attachments", $attachments, $this->context );
	}

	private function prepareCc( array $headers, WP_Post $post = null ): array {

		$cc = [];

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post ) {
			$cc = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_cc', true ) ?: [] ;
		}

		$cc = apply_filters( "juvo_mail_editor_{$this->trigger}_cc", $cc, $this->context );
		$cc = apply_filters( 'juvo_mail_editor_after_cc_placeholder', $this->parseToCcBcc( $cc, "Cc:" ), $this->trigger, $this->context );

		return array_merge( $headers, $cc );
	}

	private function prepareBcc( array $headers, WP_Post $post = null ): array {

		$bcc = [];

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post ) {
			$bcc = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_bcc', true ) ?: [];
		}

		$bcc = apply_filters( "juvo_mail_editor_{$this->trigger}_bcc", $bcc, $this->context );
		$bcc = apply_filters( 'juvo_mail_editor_after_bcc_placeholder', $this->parseToCcBcc( $bcc, "Bcc:" ), $this->trigger, $this->context );

		return array_merge( $headers, $bcc );
	}

	/**
	 * Parses cmb2 repeater groups or strings to wp_mail compatible array format for "to", "cc" and "bcc"
	 *
	 * @param array|string $recipients
	 * @param string $prefix "Cc:" or "Bcc:"
	 *
	 * @return array
	 */
	private function parseToCcBcc( $recipients, string $prefix = "" ): array {

		if ( empty( $recipients ) ) {
			return [];
		}

		if ( is_string( $recipients ) ) {
			$recipients = explode( ",", $recipients );
		}

		foreach ( $recipients as &$recipient ) {

			if ( ! empty( $recipient['name'] ) && ! empty( $recipient['mail'] ) ) {
				$recipient = "{$recipient['name']} <{$recipient['mail']}>";
			} elseif ( empty( $recipient['name'] ) && ! empty( $recipient['mail'] ) ) {
				$recipient = $recipient['mail'];
			}

			$recipient = Placeholder::replacePlaceholder( $this->placeholders, $recipient, $this->context );

			if ( ! empty( $prefix ) ) {
				$recipient = $prefix . " " . $recipient;
			}
		}

		return $recipients;

	}

}
