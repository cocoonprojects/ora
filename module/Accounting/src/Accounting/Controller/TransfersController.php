<?php

namespace Accounting\Controller;


use Accounting\Service\AccountService;
use Application\Controller\OrganizationAwareController;
use Application\Service\UserService;
use People\Service\OrganizationService;
use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use Zend\I18n\Validator\Float;
use Zend\Validator\EmailAddress;
use Zend\Validator\GreaterThan;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\ValidatorChain;

abstract class TransfersController extends OrganizationAwareController
{

	protected static $collectionOptions = [];
	protected static $resourceOptions = ['POST'];
	/**
	 * @var AccountService
	 */
	private $accountService;
	/**
	 * @var ValidatorChain
	 */
	protected $amountValidator;
	/**
	 * @var ValidatorChain
	 */
	protected $descriptionValidator;
	/**
	 * @var FilterChain
	 */
	protected $descriptionFilter;
	/**
	 * @var ValidatorChain
	 */
	protected $payeeValidator;
	/**
	 * @var UserService
	 */
	protected $userService;

	public function __construct(AccountService $accountService, UserService $userService, OrganizationService $organizationService)
	{
		parent::__construct($organizationService);
		$this->accountService = $accountService;
		$this->userService = $userService;
		$this->amountValidator = new ValidatorChain();
		$this->amountValidator
			->attach(new NotEmpty())
			->attach(new Float())
			->attach(new GreaterThan(['min' => 0, 'inclusive' => false]));
		$this->descriptionValidator = new ValidatorChain();
		$this->descriptionValidator
			->attach(new NotEmpty())
			->attach(new StringLength(['max' => 256]));
		$this->descriptionFilter = new FilterChain();
		$this->descriptionFilter
			->attach(new StringTrim())
			->attach(new StripNewlines())
			->attach(new StripTags());
		$this->payeeValidator = new ValidatorChain();
		$this->payeeValidator
			->attach(new NotEmpty())
			->attach(new EmailAddress());
	}

	/**
	 * @return AccountService
	 * @codeCoverageIgnore
	 */
	public function getAccountService()
	{
		return $this->accountService;
	}

	/**
	 * @return array
	 * @codeCoverageIgnore
	 */
	protected function getCollectionOptions()
	{
		return self::$collectionOptions;
	}

	/**
	 * @return array
	 * @codeCoverageIgnore
	 */
	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}
}