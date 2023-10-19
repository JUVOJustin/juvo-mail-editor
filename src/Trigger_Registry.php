<?php

namespace JUVO_MailEditor;

class Trigger_Registry {

	private static ?Trigger_Registry $instance = null;
	private array $storage = [];

	private function __construct() {

		add_action('init', function() {
			$this->registerTriggers();
		}, 99);

	}

	public static function getInstance(): Trigger_Registry {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function set(string $name, string $slug, string $mailHook = '') {
		$this->storage[$slug] = new Trigger($name, $slug, $mailHook);
	}

	/**
	 * @param string $key
	 *
	 * @return Trigger|null
	 */
	public function get(string $key) {
		return $this->storage[$key] ?? null;
	}

	public function getAll():array {
		return $this->storage;
	}

	/**
	 * Legacy function to register triggers that do not use the factory
	 *
	 * @return void
	 * @Deprecated
	 */
	public function registerTriggers() {
		$legacy_triggers = apply_filters("juvo_mail_editor_trigger", []);

		foreach($legacy_triggers as $trigger) {
			$this->set($trigger->getName(), $trigger->getSlug(), $trigger->getMailHook());
		}
	}

}
