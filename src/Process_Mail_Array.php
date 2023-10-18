<?php

namespace JUVO_MailEditor;

use WP_Post;
use WP_Term;

class Process_Mail_Array {

	private $args = [];

	/**
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

		$this->args = $args;

		foreach ($args['headers'] as $header) {
			if (strpos($header, 'X-JUVO-ME-Trigger:') !== false) {
				$value = trim(explode(':', $header, 2)[1]);

				// Get Mail array
				$mailArrays = $this->buildMailArrays($value);

				// If mail array is empty return original mail args
				if(!empty($mailArrays)) {

					// Send all but one. The last one is returned as args
					// If mails are send with wp_mail this method should be unhooked to avoid endless loops
					if (count($mailArrays) === 1) {
						return $mailArrays[0];
					} else {

						// Avoid endless loop, so unhook
						remove_filter('wp_mail', array($this, 'process'), 10);

						// Send all but first one
						$first = array_shift($mailArrays);

						// Send all others
						foreach ($mailArrays as $mailArray) {
							wp_mail($mailArray['to'], $mailArray['subject'], $mailArray['message'], $mailArray['headers'], $mailArray['attachments']);
						}

						// Add unhooked code again
						add_filter('wp_mail', array($this, 'process'), 10, 1);

						return $first;
					}
				}


				break;
			}
		}

		return $args;

	}

	public function buildMailArrays(string $slug): array {

		// Get Trigger from Registry
		$trigger = Trigger_Registry::getInstance()->get($slug);

		// Add Muted Capability
		if ( $trigger->isMuted() ) {
			return [];
		}

		// Store blog language defaults
		restore_previous_locale();
		$blogLocale = get_locale();

		$mails = [];
		$templates = $trigger->getRelatedPosts();

		if ($templates) {

			$locale = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_language", $blogLocale, $trigger->getContext());
			$lang   = locale_get_primary_language( $locale );

			// Switch language context
			$switched_locale = switch_to_locale( $locale );

			// Todo iterate over templates and build mils

			foreach($templates as $template) {

				// Get translated post with WPML
				$translationId = apply_filters( 'wpml_object_id', $template->ID, Mails_PT::POST_TYPE_NAME, true, $lang );
				if ( $translationId && $translationId !== $template->ID && get_post_status( $translationId ) === 'publish' ) {
					$template = get_post( $translationId );
				}

				// Todo Validate if array data is set.
				$mails[] = $this->buildMailArray($trigger, $template);
			}

			// Restore language context
			if ( $switched_locale ) {
				restore_previous_locale();
			}

		}

		return $mails;

	}

	public function buildMailArray(Trigger $trigger, WP_Post $post = null): array {

		$new_args = [
			'to' => $this->prepareRecipients( $trigger, $post ),
			'subject' => $this->prepareSubject( $trigger, $post ),
			'message' => $this->prepareContent( $trigger, $post ),
			'headers' => $this->prepareHeaders( $trigger, $post ),
			'attachments' => $this->prepareAttachments( $trigger, $post ),
		];

		// Replace empty args with original args
		foreach ($new_args as $key => $value) {
			if (empty($value)) {
				$new_args[$key] = $this->args[$key];
			}
		}

		return $new_args;
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

		$recipients = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_recipients", $recipients, $trigger->getContext() );

		return apply_filters( 'juvo_mail_editor_after_recipients_placeholder', $this->parseToCcBcc( $trigger, $recipients ), $trigger->getSlug(), $trigger->getContext() );
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

		$content = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_message", $content, $trigger->getContext() );
		$content = Placeholder::replacePlaceholder( $this->preparePlaceholders($trigger), $content, $trigger->getContext() );
		$content = apply_filters( 'juvo_mail_editor_after_content_placeholder', $content, $trigger->getSlug(), $trigger->getContext() );

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

		$subject = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_subject", $subject, $trigger->getContext() );
		$subject = Placeholder::replacePlaceholder( $this->preparePlaceholders($trigger), $subject, $trigger->getContext() );

		return apply_filters( 'juvo_mail_editor_after_subject_placeholder', $subject, $trigger->getSlug(), $trigger->getContext() );
	}

	/**
	 * @param Trigger $trigger
	 *
	 * @return array
	 */
	public function preparePlaceholders(Trigger $trigger): array {
		return apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_placeholders", array(), $trigger->getContext() );
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
		$headers = $this->prepareCc( $headers, $trigger, $post );
		$headers = $this->prepareBcc( $headers, $trigger, $post );

		return apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_headers", $headers, $trigger->getContext() );
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

		return apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_attachments", $attachments, $trigger->getContext() );
	}

	private function prepareCc( array $headers, Trigger $trigger, WP_Post $post = null ): array {

		$cc = [];

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post ) {
			$cc = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_cc', true ) ?: [] ;
		}

		$cc = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_cc", $cc, $trigger->getContext() );
		$cc = apply_filters( 'juvo_mail_editor_after_cc_placeholder', $this->parseToCcBcc( $trigger, $cc, "Cc:" ), $trigger->getSlug(), $trigger->getContext() );

		return array_merge( $headers, $cc );
	}

	private function prepareBcc( array $headers, Trigger $trigger, WP_Post $post = null ): array {

		$bcc = [];

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post ) {
			$bcc = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_bcc', true ) ?: [];
		}

		$bcc = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_bcc", $bcc, $trigger->getContext() );
		$bcc = apply_filters( 'juvo_mail_editor_after_bcc_placeholder', $this->parseToCcBcc( $trigger, $bcc, "Bcc:" ), $trigger->getSlug(), $trigger->getContext() );

		return array_merge( $headers, $bcc );
	}

	/**
	 * Parses cmb2 repeater groups or strings to wp_mail compatible array format for "to", "cc" and "bcc"
	 *
	 * @param Trigger $trigger
	 * @param array|string $recipients
	 * @param string $prefix "Cc:" or "Bcc:"
	 *
	 * @return array
	 */
	private function parseToCcBcc( Trigger $trigger, $recipients, string $prefix = "" ): array {

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

			$recipient = Placeholder::replacePlaceholder( $this->preparePlaceholders($trigger), $recipient, $trigger->getContext() );

			if ( ! empty( $prefix ) ) {
				$recipient = $prefix . " " . $recipient;
			}
		}

		return $recipients;

	}

}
