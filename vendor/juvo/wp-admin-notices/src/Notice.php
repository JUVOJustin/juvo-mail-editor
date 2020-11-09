<?php
namespace juvo\WordPressAdminNotices;

class Notice extends Manager {

    protected $id;
    protected $title;
    protected $message;
    protected $options;
    protected $creationTime;

    public function __construct(string $id, string $title, string $message, array $options = []) {
        $this->id = $id;
        $this->title = $title;
        $this->message = $message;
        $this->options = $options;
        $this->creationTime = time();
    }

    /**
     * Determines if scope is global
     *
     * @return bool
     */
    protected function isGlobalScope(): bool {
        if(array_key_exists("scope", $this->options) && $this->options["scope"] != "global") {
            return false;
        }
        return true;
    }
}