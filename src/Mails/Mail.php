<?php

namespace JUVO_MailEditor\Mails;

interface Mail {

	public function getSubject(): string;

	public function getMessage(): string;

	public function getRecipient(): string;

	/**
	 * Return default placeholder set
	 *
	 * @return array
	 */
	public function getDefaultPlaceholder(): array;

	public function getAlwaysSent(): bool;

}
