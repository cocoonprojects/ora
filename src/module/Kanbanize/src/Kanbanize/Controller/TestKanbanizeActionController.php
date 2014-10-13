<?php

namespace Kanbanize\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Client;

/**
 * TestKanbanizeActionController
 *
 * @author
 *
 * @version
 *
 */
class TestKanbanizeActionController extends AbstractActionController {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		
		$client = new Client();
		$method = $this->params()->fromQuery('method', 'get');
		$client = $client->setAdapter('Zend\Http\Client\Adapter\Curl')->setUri('http://localhost/kanbanize/task');
		switch($method) {
			case 'update':
		
//                 $data = array('boardid'=>'3');
//                 $adapter = $client->getAdapter();
                 
//                 $adapter->connect('localhost', 80);
//                 $uri = "http://localhost/kanbanize/task".'?id=59';
//                 // send with PUT Method, with $data parameter
//                 $adapter->write('PUT', new \Zend\Uri\Uri($uri), 1.1, array(), http_build_query($data)); 
                 
//                 $responsecurl = $adapter->read();
//                 list($headers, $content) = explode("\r\n\r\n", $responsecurl, 2);
//                 $response = $this->getResponse();
                  
//                 $response->getHeaders()->addHeaderLine('content-type', 'text/html; charset=utf-8');
//                 $response->setContent($content);
                 
//                 return $response;

				$data = array("boardid" => "3");
				$ch = curl_init('http://192.168.56.111/kanbanize/task/'.'10');
				
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
				
				$response = curl_exec($ch);
// 				if(!$response) {
// 					return false;
// 				}
				
		}
		
		$view = new ViewModel(array('response' => $response,"curl"=>$ch));
		
		//$view ->setTemplate("kanbanize/kanbanize/");
		return $view;
	}
}