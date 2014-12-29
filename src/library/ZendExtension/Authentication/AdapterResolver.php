<?php
namespace ZendExtension\Authentication;

use Zend\Mvc\Controller\AbstractController;

interface AdapterResolver {
	
	public function getAdapter(AbstractController $controller);
	
	public function getProviders();
	
}