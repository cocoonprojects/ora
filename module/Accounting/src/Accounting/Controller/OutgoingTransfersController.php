<?php

namespace Accounting\Controller;


use Accounting\IllegalAmountException;

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
		$identity = $this->identity()['user'];

		if (!isset($data['amount']) || !$this->amountValidator->isValid($data['amount'])) {
			$this->response->setStatusCode(400);
			return $this->response;
		}
		$amount = $data['amount'];

		if (!isset($data['description'])) {
			$this->response->setStatusCode(400);
			return $this->response;
		}
		$description = $this->descriptionFilter->filter($data['description']);
		if (!$this->descriptionValidator->isValid($description)) {
			$this->response->setStatusCode(400);
			return $this->response;
		}

		if (!isset($data['payee'])
			|| !$this->payeeValidator->isValid($data['payee'])
			|| is_null($payee = $this->userService->findUserByEmail($data['payee']))) {
			$this->response->setStatusCode(400);
			return $this->response;
		}

		$account = $this->getAccountService()->getAccount($id);
		if (is_null($account)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}

		if (!$this->isAllowed($identity, $account, 'Accounting.Account.outgoing-transfer')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$a = $this->getAccountService()->findPersonalAccount($payee, $this->organization);    // TODO: Allowing json query in event stream this call should be useless
		if (is_null($a)) {
			$this->response->setStatusCode(400);
			return $this->response;
		}
		$payeeAccount = $this->getAccountService()->getAccount($a->getId());

		$this->transaction()->begin();
		try {
			$account->transferOut(-$amount, $payeeAccount, $description, $identity);
			$payeeAccount->transferIn($amount, $account, $description, $identity);
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
		} catch (IllegalAmountException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(400);
		}
		return $this->response;
	}
}