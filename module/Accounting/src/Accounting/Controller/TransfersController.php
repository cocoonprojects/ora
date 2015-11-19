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
	 * @var FilterChain
	 */
	protected $descriptionFilter;
	/**
	 * @var UserService
	 */
	protected $userService;

	public function __construct(AccountService $accountService, UserService $userService, OrganizationService $organizationService)
	{
		parent::__construct($organizationService);
		$this->accountService = $accountService;
		$this->userService = $userService;
		$this->descriptionFilter = new FilterChain();
		$this->descriptionFilter
			->attach(new StringTrim())
			->attach(new StripNewlines())
			->attach(new StripTags());
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