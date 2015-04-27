<?php
namespace ZFX\Rest\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Json\Json;
use Application\Controller\AbstractHATEOASRestfulController;

abstract class AbstractChildController extends AbstractHATEOASRestfulController
{
	public function onDispatch(MvcEvent $e)
	{
		$routeMatch = $e->getRouteMatch();
		$response = $e->getResponse();
		if(!$this->loadParent($routeMatch->getParam('parentId'))) {
			$response->setStatusCode(404);
			return $response;
		}
		return parent::onDispatch($e);
	}
	/**
	 * 
	 * @param string $id
	 * @return null|Object;
	 */
	abstract protected function loadParent($id);
}