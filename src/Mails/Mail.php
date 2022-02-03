<?php

namespace JUVO_MailEditor\Mails;

interface Mail {

	public function getSubject( string $subject ): string;

	public function getMessage( string $message ): string;

	public function getRecipients( array $recipients ): array;

	public function getAlwaysSent(): bool;

}
