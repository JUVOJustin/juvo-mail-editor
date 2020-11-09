<?php

namespace juvo\WordPressAdminNotices;

class Manager
{

    const TRANSIENT = "juvo_notices";

    // Maximum age that a notice stored in the transient can have
    private $max_age;

    /**
     * Manager constructor.
     * @param int $max_age maximum age that a notice can have before it is removed from the transient
     */
    public function __construct(int $max_age = 60*60*24*7) {

        $this->max_age=$max_age;

        add_action('wp_ajax_wptrt_dismiss_notice', [$this, 'ajax_maybe_remove_from_transient']);
    }

    /**
     * Adds notice to transient.
     *
     * @param string $id
     * @param string $title
     * @param string $message
     * @param array $options
     */
    public static function add(string $id, string $title, string $message, array $options = []): void {

        $notices[] = new Notice($id, $title, $message, $options);;

        // Get already stored notices
        $stored = (new self)->getTransient();

        // Add
        if (!empty($stored)) {
            $notices = array_merge($notices, $stored);
        }

        // Save notices
        set_transient(self::TRANSIENT, $notices, 0);
    }

    /**
     * Passes stored notices to WPTRT\AdminNotices
     */
    public function notices(): void {
        $notices = new \WPTRT\AdminNotices\Notices();
        foreach ($this->getTransient() as $notice) {

            // Delete Notice if is older than MAX_AGE
            if (time() - $notice->creationTime > $this->max_age) {
                $this->removeFromTransient($notice->id);
                continue;
            }

            $notices->add($notice->id, $notice->title, $notice->message, $notice->options);
        }
        $notices->boot();
    }

    /**
     * Ajax Dismiss Handler to maybe delete notice from transient
     */
    public function ajax_maybe_remove_from_transient(): void {

        // Sanity check: Early exit if we're not on a wptrt_dismiss_notice action.
        if (!isset($_POST['action']) || 'wptrt_dismiss_notice' !== $_POST['action'] || !isset($_POST['id'])) {
            return;
        }

        // Security check: Make sure nonce is OK.
        check_ajax_referer('wptrt_dismiss_notice_' . $_POST['id'], 'nonce', true);

        $this->removeFromTransient($_POST["id"], true);
    }

    /**
     * Deletes notices from transient if dismissed and scope is global
     *
     * @param string $id id of notice to delete
     */
    private function removeFromTransient(string $id, bool $onlyGlobal = false): void {
        $notices = $this->getTransient();

        foreach($notices as $key => $notice) {
            if ($notice->id != $id ) {
                continue;
            }

            if ($onlyGlobal === true && !$notice->isGlobalScope()) {
                continue;
            }

            unset($notices[$key]);
        }

        if (empty($notices)) {
            delete_transient(self::TRANSIENT);
        } else {
            $this->setTransient($notices);
        }
    }

    /**
     * Save transient. Updates if transient already exists
     *
     * @param $value
     * @param int $expiration
     */
    private function setTransient(array $value, int $expiration = 0): void {
        set_transient(self::TRANSIENT, $value, 0);
    }

    /**
     * Return value of transient
     *
     * @return array of notice objects or empty array
     */
    private function getTransient(): array {
        $value = get_transient(self::TRANSIENT);

        if (empty($value)) {
            return [];
        }
        return $value;
    }

}