<?php
namespace Accounting\Controller;

use People\Service\OrganizationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
	/**
	 * @var OrganizationService
	 */
	private $organizationService;

	/**
	 * IndexController constructor.
	 * @param OrganizationService $organizationService
	 */
	public function __construct(OrganizationService $organizationService)
	{
		$this->organizationService = $organizationService;
	}

	public function indexAction()
	{
		$orgId = $this->params('orgId');
		$organization = $this->organizationService->findOrganization($orgId);
		if (is_null($organization)){
			$this->response->setStatusCode(404);
		}
		$this->layout()->setVariable('organization', $organization);
		return ['organization' => $organization];
	}

}