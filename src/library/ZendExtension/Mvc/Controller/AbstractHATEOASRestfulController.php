<?php
namespace ZendExtension\Mvc\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\EventManager\EventManagerInterface;

abstract class AbstractHATEOASRestfulController extends AbstractRestfulController
{
	protected static $JSON_CONTENT_TYPE = array('application/json','text/json');
	
	// Tells the consumer what HTTP methods are available for collection and resource
	public function options()
	{
		if ($this->params('id', false)) {
			$options = $this->getResourceOptions();
		} else {
			$options = $this->getCollectionOptions();
		}
		$response = $this->getResponse();
		$response->getHeaders()->addHeaderLine('Content-Type', 'application/hal+json');
		$response->getHeaders()->addHeaderLine('Allow', implode(',', $options));
		return $response;
	}

	public function checkOptions($e)
	{
		if ($this->params('id', false)) {
			$options = $this->getResourceOptions();
		} else {
			$options = $this->getCollectionOptions();
		}

		$method = $e->getRequest()->getMethod();
		if (in_array($method, $options) || $method == 'OPTIONS') {
			// HTTP method is allowed!
			return;
		}
		
		$response = $this->getResponse();
		$response->getHeaders()->addHeaderLine('Content-Type', 'application/hal+json');
		$response->setStatusCode(405); // Method Not Allowed
		
		return $response;
	}

	public function preDispatch($e)	{}
	
	public function setEventManager(EventManagerInterface $events)
	{
		parent::setEventManager($events);
		
		// Register a listener at high priority
		$events->attach('dispatch', array($this, 'checkOptions'), 10);
		$events->attach('dispatch', array($this, 'preDispatch'), 20);
	}

	protected abstract function getCollectionOptions();
	
	protected abstract function getResourceOptions();

}