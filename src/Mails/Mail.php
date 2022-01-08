<?php

namespace JUVO_MailEditor\Mails;

interface Mail {

	public function getSubject(): string;

	public function getMessage(): string;

	public function getRecipient(): string;

	public function getAlwaysSent(): bool;

}
