<?php
namespace ZFX\Rest\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Json\Json;
use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\Controller\AbstractController;

abstract class HATEOASRestfulController extends AbstractRestfulController
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
	
	public function checkOptions(MvcEvent $e)
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

	public function setEventManager(EventManagerInterface $events)
	{
		parent::setEventManager($events);
		
		// Register a listener at high priority
		$events->attach('dispatch', array($this, 'checkOptions'), 10);
	}
	//Supporting POST with id set
	public function onDispatch(MvcEvent $e)
	{
		$routeMatch = $e->getRouteMatch();
		if (!$routeMatch || $routeMatch->getParam('action', false)) {
			return parent::onDispatch($e);
		}
		$request = $e->getRequest();
		// RESTful methods
		$method = strtolower($request->getMethod());
		switch ($method) {
			case 'post':
				$id = $this->getIdentifier($routeMatch, $request);
				if ($id !== false) {
					$action = 'invoke';
					$return = $this->processPostWithIdData($id, $request);
					break;
				}
			default:
				$return = parent::onDispatch($e);
				break;
		}
		$e->setResult($return);
		return $return;
	}
	
	/**
	 * POST with ID
	 *
	 * @param  mixed $id
	 * @param  mixed $data
	 * @return mixed
	 */
	public function invoke($id, $data)
	{
		$this->response->setStatusCode(405);
	
		return array(
				'content' => 'Method Not Allowed'
		);
	}

	/**
	 * Process post data and call create
	 *
	 * @param Request $request
	 * @return mixed
	 */
	protected function processPostWithIdData($id, Request $request)
	{
		if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
			$data = Json::decode($request->getContent(), $this->jsonDecodeType);
		} else {
			$data = $request->getPost()->toArray();
		}
	
		return $this->invoke($id, $data);
	}
	
	protected abstract function getCollectionOptions();
	
	protected abstract function getResourceOptions();
	
	public function dispatch(Request $request, Response $response = null)
    {
        if ($request instanceof ConsoleRequest) {
           return AbstractController::dispatch($request, $response);
        }

        return parent::dispatch($request, $response);
    }
}