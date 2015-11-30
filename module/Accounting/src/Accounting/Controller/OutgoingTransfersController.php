<?php

namespace Accounting\Controller;


use Accounting\IllegalAmountException;
use Application\View\ErrorJsonModel;
use Zend\I18n\Validator\Float;
use Zend\Validator\EmailAddress;
use Zend\Validator\GreaterThan;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\ValidatorChain;
use Zend\View\Model\JsonModel;

class OutgoingTransfersController extends TransfersController
{
	/**
	 * POST with ID
	 *
	 * @param  mixed $id
	 * @param  mixed $data
	 * @return mixed
	 */
	public function invoke($id, $data)
	{
		if (is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$error = new ErrorJsonModel();
		$amountValidator = new ValidatorChain();
		$amountValidator
				->attach(new NotEmpty())
				->attach(new Float())
				->attach(new GreaterThan(['min' => 0, 'inclusive' => false]));
		if(!isset($data['amount'])) {
			$error->addSecondaryErrors('amount', ['amount is required. It must be a float strictly greater than 0']);
		} elseif(!$amountValidator->isValid($data['amount'])) {
			$error->addSecondaryErrors('amount', $amountValidator->getMessages());
		}

		$descriptionValidator = new ValidatorChain();
		$descriptionValidator
				->attach(new NotEmpty())
				->attach(new StringLength(['max' => 256]));
		$description = null;
		if(!isset($data['description'])) {
			$error->addSecondaryErrors('description', ['description is required. It must be 256 characters long at most']);
		} else {
			$description = $this->descriptionFilter->filter($data['description']);
			if (!$descriptionValidator->isValid($description)) {
				$error->addSecondaryErrors('description', $descriptionValidator->getMessages());
			}
		}

		$payeeValidator = new ValidatorChain();
		$payeeValidator
				->attach(new NotEmpty())
				->attach(new EmailAddress());
		if(!isset($data['payee'])) {
			$error->addSecondaryErrors('payee', ['payee email is required. It must be the email address of an organization member']);
		} elseif(!$payeeValidator->isValid($data['payee'])) {
			$error->addSecondaryErrors('payee', $payeeValidator->getMessages());
		} elseif(is_null($payee = $this->userService->findUserByEmail($data['payee']))) {
			$error->addSecondaryErrors('payee', ['email not found. It must be the email address of an organization member']);
		}

		if($error->hasErrors()) {
			$error->setCode(400);
			$error->setDescription('Some parameters are not valid');
			$this->response->setStatusCode(400);
			return $error;
		}

		$account = $this->getAccountService()->getAccount($id);
		if (is_null($account)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}

		if (!$this->isAllowed($this->identity(), $account, 'Accounting.Account.outgoing-transfer')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$a = $this->getAccountService()->findPersonalAccount($payee, $this->organization);    // TODO: Allowing json query in event stream this call should be useless
		if (is_null($a)) {
			$this->response->setStatusCode(400);
			return $this->response;
		}
		$payeeAccount = $this->getAccountService()->getAccount($a->getId());

		$amount = $data['amount'];
		$this->transaction()->begin();
		try {
			$transactions[] = $account->transferOut(-$amount, $payeeAccount, $description, $this->identity());
			$transactions[] = $payeeAccount->transferIn($amount, $account, $description, $this->identity());
			$this->transaction()->commit();
			$this->response->setStatusCode(201); // Created
			$this->response->getHeaders()->addHeaderLine(
				'Location',
				$this->url()->fromRoute('accounts', [
					'orgId' => $account->getOrganizationId(),
					'id' => $account->getId(),
					'controller' => 'statements'
				])
			);
			$view = new JsonModel([
					'_embedded' => [
							'ora:transaction' => $transactions
					],
					'count' => 2,
					'total' => 2
			]);
			return $view;
		} catch (IllegalAmountException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(400);
		}
		return $this->response;
	}
}
