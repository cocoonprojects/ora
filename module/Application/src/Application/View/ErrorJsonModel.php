<?php
namespace Application\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class ErrorJsonModel extends JsonModel {
	
	public static $ERROR_INPUT_VALIDATION = 1024;
	
	private static $ERROR_MESSAGES = [
		403 => 'Forbidden',
		412 => 'Precondition failed',
		1024 => 'Input validation failed',
	];
	
	private $errors = array();
	
	/**
	 * 
	 * @param int $code
	 * @return $this
	 */
	public function setCode($code) {
		$this->errors['code'] = $code;
		$this->errors['message'] = isset(self::$ERROR_MESSAGES[$code]) ? self::$ERROR_MESSAGES[$code] : null;
		return $this;
	}

	/**
	 * 
	 * @param string $field
	 * @param array $messages
	 * @return $this
	 */
	public function addSecondaryErrors($field, $messages) {
		foreach($messages as $message) {
			$this->errors['errors'][] = [
					'field' => $field,
					'message' => $message,
			];
		}
		return $this;
	}

	/**
	 * @param string $description how to fix the error
	 * @return $this
	 */
	public function setDescription($description) {
		$this->errors['description'] = $description;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasErrors() {
		return count($this->errors) > 0;
	}

	/**
	 * @return string
	 */
	public function serialize() {
		return Json::encode($this->errors);
	}
}