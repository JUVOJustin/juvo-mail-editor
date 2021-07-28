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
	 */
	public function __construct( string $trigger, array $placeholders, $context = null ) {
		$this->trigger      = $trigger;
		$this->context      = $context;
		$this->term         = $this->setTerm();
		$this->posts        = $this->setPostsForTrigger();
		$this->placeholders = $this->preparePlaceholders( $placeholders );
	}

	public function sendMails() {

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
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function prepareContent( WP_Post $post ): string {

		$content = $post->post_content ?: get_term_meta( $this->term->term_id, Mail_Trigger_TAX::TAXONOMY_NAME . "_default_content", true );
		$blocks  = parse_blocks( $content );
		$content = '';

		foreach ( $blocks as $block ) {
			$content .= render_block( $block );
		}

		$content = apply_filters( "juvo_mail_editor_{$this->trigger}_before_content_placeholder", $content, $this->trigger, $this->context );
		$content = Placeholder::replacePlaceholder( $this->placeholders, $content, $this->context );
		$content = apply_filters( "juvo_mail_editor_{$this->trigger}_after_content_placeholder", $content, $this->trigger, $this->context );

		return $content;
	}

	/**
	 * @param WP_Post $post
	 * @param array $placeholders
	 *
	 * @return string
	 */
	public function prepareSubject( WP_Post $post ): string {

		$subject = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_subject', true ) ?: get_term_meta( $this->term->term_id, Mail_Trigger_TAX::TAXONOMY_NAME . "_default_subject", true );
		$subject = apply_filters( "juvo_mail_editor_{$this->trigger}_before_subject_placeholder", $subject, $this->trigger, $this->context );
		$subject = Placeholder::replacePlaceholder( $this->placeholders, $subject, $this->context );
		$subject = apply_filters( "juvo_mail_editor_{$this->trigger}_after_subject_placeholder", $subject, $this->trigger, $this->context );

		return $subject;
	}

	/**
	 * @param WP_Post $post
	 * @param array $placeholders
	 *
	 * @return string
	 */
	public function prepareRecipients( WP_Post $post ): string {

		$recipients   = get_post_meta( $post->ID, Mails_PT::POST_TYPE_NAME . '_recipients', true ) ?: "{{CONTEXT}}";
		$recipients   = apply_filters( "juvo_mail_editor_{$this->trigger}_before_recipient_placeholder", $recipients, $this->trigger, $this->context );
		$placeholders = $this->placeholders;
		if ( $this->context instanceof WP_User ) {
			$placeholders["context"] = $this->context->user_email;
		}
		$recipients = Placeholder::replacePlaceholder( $placeholders, $recipients, $this->context );
		$recipients = apply_filters( "juvo_mail_editor_{$this->trigger}_after_recipient_placeholder", $recipients, $this->trigger, $this->context );

		return $recipients;
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

}
