<?php

namespace hypeJunction\Prototyper\Elements;

class ValidationStatus {

	/** @var mixed */
    protected $status;
	/** @var mixed */
    protected $messages = array();

	/**
     * @param mixed $status
     * @param mixed $messages
     */
    public function __construct($status = true, $messages = array()) {
		$this->status = (bool) $status;
		$this->messages = (is_array($messages)) ? $messages : array();
	}

	/**
     * @param mixed $status
     */
    private function setStatus($status = true) {
		$this->status = $status;
	}

	/**
     * @param mixed $message
     */
    public function setFail($message = '') {
		$this->setStatus(false);
		$this->addMessage($message);
	}

	/**
     * @param mixed $message
     */
    public function setSuccess($message = '') {
		$this->setStatus(true);
		$this->addMessage($message);
	}

	/**
     * @param mixed $message
     */
    public function addMessage($message = '') {
		if ($message) {
			$this->messages[] = $message;
		}
	}

	/**
     * @return mixed
     */
    public function getStatus() {
		return (bool) $this->status;
	}

	/**
     * @return mixed
     */
    public function getMessages() {
		return $this->messages;
	}

	/**
     * @return mixed
     */
    public function isValid() {
		return $this->getStatus() !== false;
	}

}
