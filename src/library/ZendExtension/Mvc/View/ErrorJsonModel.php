<?php
namespace ZendExtension\Mvc\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class ErrorJsonModel extends JsonModel {
	
	public static $ERROR_INPUT_VALIDATION = 1024;
	
	private static $ERROR_MESSAGES = array(
		1024 => 'Input validation failed',
	);
	
	private $errors = array();
	
	/**
	 * 
	 * @param int $code
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
	 * 
	 * @param string $description how to fix the error
	 */
	public function setDescription($description) {
		$this->errors['description'] = $description;
		return $this;
	}
	
	public function hasErrors() {
		return count($this->errors) > 0;
	}
	
	public function serialize() {
		return Json::encode($this->errors);
	}
}