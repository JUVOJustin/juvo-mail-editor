<?php

namespace JUVO_MailEditor\Mails;

interface Mail {

	function getSubject(): string;

	function getMessage(): string;

	function getRecipient(): string;

	function getTrigger(): string;

	/**
	 * Return default placeholder set
	 *
	 * @return array
	 */
	public function getDefaultPlaceholder(): array;

	public function getAlwaysSent(): bool;

}
