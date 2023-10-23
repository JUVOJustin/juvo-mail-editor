<?php


namespace JUVO_MailEditor;

use WP_HTML_Tag_Processor;
use WP_Post;

class Relay {

	private $args = [];

	/**
	 * This callback hooks into wp_mail and checks if the header indicates that this mail should be processed by juvo_mail_editor.
	 * If so it will process the mail and return the mail array.
	 * If multiple templates are set up for one trigger, the first one will be returned callback and all other mails will be send directly with wp_mail in "process_trigger".
	 *
	 * @param array $args {
	 *      Array of the `wp_mail()` arguments.
	 *
	 * @return array
	 */
	public function wpmail_filter_callback( array $args ): array {

		// Trigger info lives in header. If no headers set exit early
		if ( empty( $args['headers'] ) ) {
			return $args;
		}

		// Headers can be passed as array and as string
		if (!is_array($args['headers'])) {
			$args['headers'] = explode("\r\n", $args['headers']);
		}

		$this->args = $args;

		$mailArrays = [];

		foreach ( $args['headers'] as $header ) {
			if ( strpos( $header, 'X-JUVO-ME-Trigger:' ) !== false ) {
				$value = trim( explode( ':', $header, 2 )[1] );
				$mailArrays = array_merge($mailArrays, $this->buildMailArrays( $value ));
			}
		}

		// Return first mail array item;
		if (!empty($mailArrays)) {
			$this->process_trigger( $mailArrays, true );
			return reset($mailArrays);
		}

		return $args;

	}

	/**
	 * Some triggers might not use wp_mail natively. It is totally fine to directly send the mails with juvo_mail_editor.
	 * In this case we do not hook into wp_mail but directly send the mails.
	 *
	 * Callback for the "juvo_mail_editor_send" action.
	 *
	 * @param string $trigger
	 * @param array $context
	 *
	 * @return void
	 */
	public function send_mails_action_callback( string $trigger, array $context = array() ) {

		$trigger = Trigger_Registry::getInstance()->get( $trigger );
		if ( empty( $trigger ) ) {
			return;
		}

		// Add Context
		$trigger->setContext( $context );

		$mailArrays = $this->buildMailArrays( $trigger->getSlug() );
		$this->process_trigger( $mailArrays, false );
	}

	/**
	 * Send the given mail array and optionally return the first mail array.
	 *
	 * @param array $mailArrays
	 * @param bool $return_first
	 *
	 * @return array
	 */
	public function process_trigger(  array $mailArrays, bool $return_first = false ): array {

		// Exit early if no mails are set up
		if ( empty( $mailArrays ) ) {
			return [];
		}

		// Avoid endless loop, so unhook
		remove_filter( 'wp_mail', array( $this, 'wpmail_filter_callback' ), 10 );

		// Send all others
		$i = 0;
		foreach ( $mailArrays as $key => $mailArray ) {

			// Send all but first one
			if ( $return_first && $i === 0 ) {
				$i ++;
				continue;
			}

			// Avoid endless loop, so unhook
			remove_filter( 'wp_mail', array( $this, 'wpmail_filter_callback' ), 10 );

			wp_mail( $mailArray['to'], $mailArray['subject'], $mailArray['message'], $mailArray['headers'], $mailArray['attachments'] );

			// Add unhooked code again
			add_filter( 'wp_mail', array( $this, 'wpmail_filter_callback' ), 10, 1 );

			// Remove mail from array so that either none or only one mail is returned
			unset( $mailArrays[ $key ] );
			$i ++;
		}

		return $mailArrays;
	}

	/**
	 * Build mail array for each template and returns one combined array that contains all mails to be sent.
	 * This function also takes care that translations are applied correctly for each template.
	 *
	 * @param string $slug
	 *
	 * @return array
	 */
	public function buildMailArrays( string $slug ): array {

		// Get Trigger from Registry
		$trigger = Trigger_Registry::getInstance()->get( $slug );
		if ( empty( $trigger ) ) {
			return [];
		}

		// Add Muted Capability
		if ( $trigger->isMuted() ) {
			return [];
		}

		// Store blog language defaults
		restore_previous_locale();
		$blogLocale = get_locale();

		$mails     = [];
		$templates = $trigger->getRelatedPosts();

		$locale = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_language", $blogLocale, $trigger->getContext() );
		$lang   = locale_get_primary_language( $locale );

		// Switch language context
		$switched_locale = switch_to_locale( $locale );


		if ( ! $templates ) {
			// If not template is set up try to get mail array from hooks
			$mails[] = $this->buildMailArray( $trigger );
		} else {

			// Build mail array for each template
			foreach ( $templates as $template ) {

				// Get translated post with WPML
				$translationId = apply_filters( 'wpml_object_id', $template->ID, Mails_PT::POST_TYPE_NAME, true, $lang );
				if ( $translationId && $translationId !== $template->ID && get_post_status( $translationId ) === 'publish' ) {
					$template = get_post( $translationId );
				}

				$mails[] = $this->buildMailArray( $trigger, $template );
			}

		}

		// Restore language context
		if ( $switched_locale ) {
			restore_previous_locale();
		}

		return $mails;

	}

	/**
	 * Builds an array with mail args in a way that WordPress uses internally
	 *
	 * @param Trigger $trigger
	 * @param WP_Post|null $post
	 *
	 * @return array
	 */
	public function buildMailArray( Trigger $trigger, WP_Post $post = null ): array {

		$new_args = [
			'to'          => $this->prepareRecipients( $trigger, $post ),
			'subject'     => $this->prepareSubject( $trigger, $post ),
			'message'     => $this->prepareContent( $trigger, $post ),
			'attachments' => $this->prepareAttachments( $trigger, $post ),
		];

		$new_args['headers'] = implode("\r\n", $this->prepareHeaders( $trigger, $post, $new_args['message'] ));

		// Replace empty args with original args
		foreach ( $new_args as $key => $value ) {
			if ( empty( $value ) ) {
				$new_args[ $key ] = $this->args[ $key ] ?? "";
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
	public function prepareContent( Trigger $trigger, WP_Post $post = null ): string {

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
		$content = Placeholder::replacePlaceholder( $this->preparePlaceholders( $trigger ), $content, $trigger->getContext() );

		// If html tags present use wpautop to automagically fix linebreaks
		$p = new WP_HTML_Tag_Processor( $content );
		if ($p->next_tag()) {
			$content = wpautop( $content );
		}

		$content = apply_filters( 'juvo_mail_editor_after_content_placeholder', $content, $trigger->getSlug(), $trigger->getContext() );

		return $content;
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
		$subject = Placeholder::replacePlaceholder( $this->preparePlaceholders( $trigger ), $subject, $trigger->getContext() );

		return apply_filters( 'juvo_mail_editor_after_subject_placeholder', $subject, $trigger->getSlug(), $trigger->getContext() );
	}

	/**
	 * @param Trigger $trigger
	 *
	 * @return array
	 */
	public function preparePlaceholders( Trigger $trigger ): array {
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
	private function prepareHeaders( Trigger $trigger, WP_Post $post = null, string $content = "" ) {

		$headers[] = "X-JUVO-ME-Trigger: {$trigger->getSlug()}";

		if ( $post ) {
			$headers[] = "X-JUVO-ME-PostID: {$post->ID}";
		}

		// Use new Tag Processor API to dynamically set content type
		if (!empty($content)) {
			$p = new WP_HTML_Tag_Processor( $content );
			if ($p->next_tag()) {
				$headers[] = "Content-Type: text/html; charset=UTF-8";
			}
		}

		// Add CC and BCC
		$headers = array_merge($headers, $this->prepareCc( $headers, $trigger, $post ));
		$headers = array_merge($headers, $this->prepareBcc( $headers, $trigger, $post ));

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
			$cc = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_cc', true ) ?: [];
		}

		$cc = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_cc", $cc, $trigger->getContext() );
		$cc = apply_filters( 'juvo_mail_editor_after_cc_placeholder', $this->parseToCcBcc( $trigger, $cc, "Cc:" ), $trigger->getSlug(), $trigger->getContext() );

		return $cc;
	}

	private function prepareBcc( array $headers, Trigger $trigger, WP_Post $post = null ): array {

		$bcc = [];

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( $post ) {
			$bcc = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_bcc', true ) ?: [];
		}

		$bcc = apply_filters( "juvo_mail_editor_{$trigger->getSlug()}_bcc", $bcc, $trigger->getContext() );
		$bcc = apply_filters( 'juvo_mail_editor_after_bcc_placeholder', $this->parseToCcBcc( $trigger, $bcc, "Bcc:" ), $trigger->getSlug(), $trigger->getContext() );

		return $bcc;
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

			$recipient = Placeholder::replacePlaceholder( $this->preparePlaceholders( $trigger ), $recipient, $trigger->getContext() );

			if ( ! empty( $prefix ) ) {
				$recipient = $prefix . " " . $recipient;
			}
		}

		return $recipients;

	}

}
