<?php

namespace Accounting\Controller;

use Accounting\IllegalAmountException;
use Accounting\Service\AccountService;
use Application\View\ErrorJsonModel;
use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use Zend\I18n\Validator\IsFloat;
use Zend\Validator\GreaterThan;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\ValidatorChain;
use Zend\View\Model\JsonModel;
use ZFX\Rest\Controller\HATEOASRestfulController;

class WithdrawalsController extends HATEOASRestfulController
{
	protected static $collectionOptions = [];
	protected static $resourceOptions = ['POST'];
	/**
	 *
	 * @var AccountService
	 */
	protected $accountService;
	/**
	 * @var FilterChain
	 */
	private $descriptionFilter;

	public function __construct(AccountService $accountService) {
		$this->accountService = $accountService;
		$this->descriptionFilter = new FilterChain();
		$this->descriptionFilter
				->attach(new StringTrim())
				->attach(new StripNewlines())
				->attach(new StripTags());
	}

	public function invoke($id, $data) {
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$error = new ErrorJsonModel();
		$amountValidator = new ValidatorChain();
		$amountValidator
				->attach(new NotEmpty())
				->attach(new IsFloat())
				->attach(new GreaterThan(['min' => 0, 'inclusive' => false]));
		if(!isset($data['amount'])) {
			$error->addSecondaryErrors('amount', ['amount is required. It must be a float strictly greater than 0']);
		} elseif(!$amountValidator->isValid($data['amount'])) {
			$error->addSecondaryErrors('amount', $amountValidator->getMessages());
		}

		$descriptionValidator = new ValidatorChain();
		$descriptionValidator
				->attach(new StringLength(['max' => 256]));
		$description = null;
		if(isset($data['description'])) {
			$description = $this->descriptionFilter->filter($data['description']);
			if (!$descriptionValidator->isValid($description)) {
				$error->addSecondaryErrors('description', $descriptionValidator->getMessages());
			}
		}

		if($error->hasErrors()) {
			$error->setCode(400);
			$error->setDescription('Some parameters are not valid');
			$this->response->setStatusCode(400);
			return $error;
		}

		$account = $this->accountService->getAccount($id);
		if(is_null($account)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}

		if(!$this->isAllowed($this->identity(), $account, 'Accounting.Account.withdrawal')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$this->transaction()->begin();
		try {
			$transaction = $account->withdraw(-$data['amount'], $this->identity(), $description);
			$this->transaction()->commit();
			$this->response->setStatusCode(201); // Created
			$this->response->getHeaders()->addHeaderLine(
				'Location',
				$this->url()->fromRoute('accounts', ['orgId' => $account->getOrganizationId(), 'id' => $account->getId()])
			);
			return new JsonModel($transaction);
		} catch (IllegalAmountException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(400);
		} catch (IllegalPayerException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(400);
		}

		return $this->response;
	}

	/**
	 * @return AccountService
	 * @codeCoverageIgnore
	 */
	public function getAccountService() {
		return $this->accountService;
	}

	/**
	 * @return array
	 * @codeCoverageIgnore
	 */
	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}

	/**
	 * @return array
	 * @codeCoverageIgnore
	 */
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}

}