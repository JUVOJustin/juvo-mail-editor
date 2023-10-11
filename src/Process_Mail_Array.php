<?php

namespace JUVO_MailEditor;

use WP_Post;
use WP_Term;

class Process_Mail_Array {

	/**
	 * @param bool|null $return
	 * @param array $args {
	 *      Array of the `wp_mail()` arguments.
	 *
	 * @return array
	 */
	public function process( array $args ): array {

		// Trigger info lives in header. If no headers set exit early
		if (empty($args['headers'])) {
			return $args;
		}

		foreach ($args['headers'] as $header) {
			if (strpos($header, 'X-JUVO-ME-Trigger:') !== false) {
				$value = trim(explode(':', $header, 2)[1]);

				$mailArrays = $this->buildMailArrays($value);;
				break;
			}
		}

		return $args;

	}

	public function buildMailArrays(string $slug): array {

		$triggers = apply_filters( 'juvo_mail_editor_trigger', [] );
		$trigger = array_filter($triggers, function($obj) use ($slug) {
			return $obj->getSlug() === $slug;
		});

		// Slugs have to be unique therefore they array should only contain one element
		$trigger = $trigger ? reset($trigger) : null;

		// Make sure it is an actual trigger.
		if (!$trigger instanceof Trigger) {
			return [];
		}

		// Add Muted Capability
		if ( $trigger->isMuted() ) {
			return [];
		}

		// Store blog language defaults
		restore_previous_locale();
		$blogLocale = get_locale();

		$templates = $trigger->getRelatedPosts();
		if (!$templates) {

		} else {

			$locale = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_language", $blogLocale, $relay->context );
			$lang   = locale_get_primary_language( $locale );

			// Switch language context
			$switched_locale = switch_to_locale( $locale );

			// todo process triggers
			$mails = [];

			// Restore language context
			if ( $switched_locale ) {
				restore_previous_locale();
			}


		}

		return $mails;

	}

	public function buildMailArray(WP_Post $post): array {
		return [];
	}

	/**
	 * @param WP_Post|null $post
	 * @param Trigger $trigger
	 *
	 * @return array
	 */
	public function prepareRecipients( Trigger $trigger, WP_Post $post = null ): array {

		$recipients = [];

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post ) {
			$recipients = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_recipients', true ) ?: [];
		}

		$recipients = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_recipients", $recipients, $this->context );

		return apply_filters( 'juvo_mail_editor_after_recipients_placeholder', $this->parseToCcBcc( $recipients ), $trigger->getSlug(), $this->context );
	}

	/**
	 * @param WP_Post|null $post
	 * @param Trigger $trigger
	 *
	 * @return string
	 */
	public function prepareContent( Trigger $trigger, WP_Post $post = null  ): string {

		$content = '';

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post && $content = get_the_content( null, false, $post ) ) {
			$blocks  = parse_blocks( $content );
			$content = '';

			foreach ( $blocks as $block ) {
				$content .= render_block( $block );
			}
		}

		$content = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_message", $content, $this->context );
		$content = Placeholder::replacePlaceholder( $this->preparePlaceholders($trigger), $content, $this->context );
		$content = apply_filters( 'juvo_mail_editor_after_content_placeholder', $content, $trigger->getSlug(), $this->context );

		$content = $this->setContentType( $content );

		return $content;
	}

	public function setContentType( string $message ): string {

		$type    = 'text/html';

		// If plaintext make linebreaks html compliant
		if($message == strip_tags($message)){
			$message = wpautop( $message );
		}

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
	 * @param Trigger $trigger
	 *
	 * @return string
	 */
	public function prepareSubject( Trigger $trigger, WP_Post $post = null ): string {

		$subject = '';

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post ) {
			$subject = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_subject', true );
		}

		$subject = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_subject", $subject, $this->context );
		$subject = Placeholder::replacePlaceholder( $this->preparePlaceholders($trigger), $subject, $this->context );

		return apply_filters( 'juvo_mail_editor_after_subject_placeholder', $subject, $trigger->getSlug(), $this->context );
	}

	/**
	 * @param Trigger $trigger
	 *
	 * @return array
	 */
	public function preparePlaceholders(Trigger $trigger): array {
		return apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_placeholders", array(), $this->context );
	}

	/**
	 * Sets custom headers.
	 * By defaults adds headers to identify mail-editor mails through WordPress
	 *
	 * @param WP_Post|null $post
	 *
	 * @return mixed|void
	 */
	private function prepareHeaders( Trigger $trigger, WP_Post $post = null  ) {

		$headers[] = "X-JUVO-ME-Trigger: {$trigger->getSlug()}";

		if ( $post ) {
			$headers[] = "X-JUVO-ME-PostID: {$post->ID}";
		}

		// Add CC and BCC
		$headers = $this->prepareCc( $headers, $post );
		$headers = $this->prepareBcc( $headers, $post );

		return apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_headers", $headers, $this->context );
	}

	private function prepareAttachments( Trigger $trigger, WP_Post $post = null ): array {

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

		return apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_attachments", $attachments, $this->context );
	}

	private function prepareCc( array $headers, Trigger $trigger, WP_Post $post = null ): array {

		$cc = [];

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post ) {
			$cc = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_cc', true ) ?: [] ;
		}

		$cc = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_cc", $cc, $this->context );
		$cc = apply_filters( 'juvo_mail_editor_after_cc_placeholder', $this->parseToCcBcc( $cc, "Cc:" ), $trigger->getSlug(), $this->context );

		return array_merge( $headers, $cc );
	}

	private function prepareBcc( array $headers, Trigger $trigger, WP_Post $post = null ): array {

		$bcc = [];

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post ) {
			$bcc = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_bcc', true ) ?: [];
		}

		$bcc = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_bcc", $bcc, $this->context );
		$bcc = apply_filters( 'juvo_mail_editor_after_bcc_placeholder', $this->parseToCcBcc( $bcc, "Bcc:" ), $trigger->getSlug(), $this->context );

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

			$recipient = Placeholder::replacePlaceholder( $this->preparePlaceholders(), $recipient, $this->context );

			if ( ! empty( $prefix ) ) {
				$recipient = $prefix . " " . $recipient;
			}
		}

		return $recipients;

	}

}
