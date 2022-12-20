<?php


namespace JUVO_MailEditor;

use Timber\Term;
use Timber\Timber;
use Timber\User;
use WP_Post;
use WP_Term;
use WP_User;

class Placeholder
{

	public static function replacePlaceholder(array $placeholder, string $text, array $context = array()) {

		foreach ($placeholder as $key => $value) {

			// Check if is callback
			if (is_callable($value)) {
				$placeholder[$key] = call_user_func($value);
			} else {
				// If not same the value in case it is not an array
				$placeholder[$key] = is_array($value) ? '' : $value;
			}

			$text = str_replace('{{' . $key . '}}', $value, $text); // Without space before brackets
			$text = str_replace('{{ ' . $key . ' }}', $value, $text);
		}

		// Parse context for timber
		$renderContext = Timber::context();
		apply_filters('juvo_mail_editor_timber_context', $renderContext);

		foreach ($context as $key => $item) {
			if ($item instanceof WP_User) {
				$renderContext[$key] = new User($item);
			} elseif ($item instanceof WP_Post) {
				$renderContext[$key] = Timber::get_post($item->ID);
			} elseif ($item instanceof WP_Term) {
				$renderContext[$key] = new Term($item->term_id);
			} else {
				$renderContext[$key] = $item;
			}
		}

		// Parse text with timber/twig to add logic and advanced placeholder support
		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ($compiled = Timber::compile_string($text, $renderContext)) {
			return $compiled;
		}

		return $text;
	}

	/**
	 * Remove some of the default context variables timber sets
	 *
	 * @param array $timber_context
	 *
	 * @return array
	 */
	public function filterTimberContext(array $timber_context): array {

		$timber_context = json_decode(json_encode($timber_context), true);

		unset($timber_context['body_class']);
		unset($timber_context['request']);
		unset($timber_context['wp_head']);
		unset($timber_context['wp_footer']);
		unset($timber_context['posts']);

		$to_delete_attributes = [
			'session_tokens',
			'user_activation_key',
			'wp_persisted_preferences',
			'wp_dashboard_quick_press_last_post_id'
		];

		$this->array_walk_recursive_delete($timber_context, function($item, $key) use($to_delete_attributes): bool {

			// Session tokens not needed for mails
			if (in_array($key, $to_delete_attributes)) {
				return true;
			}

			// Remove meta fields starting with underscore since they are considered hidden
			if (str_starts_with($key, '_')) {
				return true;
			}

			if (
				str_starts_with($key, 'closedpostboxes_')
				|| str_starts_with($key, 'metaboxhidden_')
			) {
				return true;
			}

			return false;

		});

		return $timber_context;

	}

	/**
	 * Remove any elements where the callback returns true
	 *
	 * @param  array    $array    the array to walk
	 * @param  callable $callback callback takes ($value, $key, $userdata)
	 * @param  mixed    $userdata additional data passed to the callback.
	 * @return array
	 */
	private function array_walk_recursive_delete(array &$array, callable $callback, $userdata = null) {
		foreach ($array as $key => &$value) {
			if (is_array($value)) {
				$value = $this->array_walk_recursive_delete($value, $callback, $userdata);
			}
			if ($callback($value, $key, $userdata)) {
				unset($array[$key]);
			}
		}

		return $array;
	}
}
