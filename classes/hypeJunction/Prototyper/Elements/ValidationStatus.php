<?php

namespace hypeJunction\Prototyper\Elements;

/**
 * Validation status DTO.
 */
class ValidationStatus {

	protected $status;

	protected $messages = [];

	/**
	 * Constructor
	 *
	 * @param bool  $status   Initial pass/fail flag
	 * @param array $messages Initial messages
	 */
	public function __construct($status = true, $messages = []) {
		$this->status = (bool) $status;
		$this->messages = (is_array($messages)) ? $messages : [];
	}

	/**
	 * Set status flag
	 *
	 * @param bool $status Pass/fail flag
	 * @return void
	 */
	private function setStatus($status = true) {
		$this->status = $status;
	}

	/**
	 * Mark validation as failed and append a message
	 *
	 * @param string $message Failure message
	 * @return void
	 */
	public function setFail($message = '') {
		$this->setStatus(false);
		$this->addMessage($message);
	}

	/**
	 * Mark validation as succeeded and append a message
	 *
	 * @param string $message Success message
	 * @return void
	 */
	public function setSuccess($message = '') {
		$this->setStatus(true);
		$this->addMessage($message);
	}

	/**
	 * Append a message
	 *
	 * @param string $message Message to append
	 * @return void
	 */
	public function addMessage($message = '') {
		if ($message) {
			$this->messages[] = $message;
		}
	}

	/**
	 * Get pass/fail flag
	 *
	 * @return bool
	 */
	public function getStatus() {
		return (bool) $this->status;
	}

	/**
	 * Get accumulated messages
	 *
	 * @return array
	 */
	public function getMessages() {
		return $this->messages;
	}

	/**
	 * Check if status is valid
	 *
	 * @return bool
	 */
	public function isValid() {
		return $this->getStatus() !== false;
	}
}
