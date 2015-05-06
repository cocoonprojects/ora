<?php 

namespace TaskManagement\Service;

use Zend\View\Renderer\PhpRenderer;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Renderer\Zend\View\Renderer;

class NotificationService{
	
	
	/**
	 *
	 * @var array of email template paths
	 */
	private $emailTemplates;
	
	public function __construct($emailTemplates){
		//TODO: come controllo che sia effettivamente corretto $emailTemplates?
		$this->emailTemplates = $emailTemplates;
	}
	
	
	public function sendEmailNotificationForShareAssignment($params){
				
		if(isset($params['name']) 
				&& isset($params['taskSubject']) 
				&& isset($params['taskId']) 
				&& isset($params['emailAddress'])
				&& isset($params['url'])){
			
			$renderer = new PhpRenderer();
			$viewModel = new ViewModel();
			$resolver = new TemplateMapResolver();
			$resolver->setMap($this->emailTemplates);
			$renderer->setResolver($resolver);
			$viewModel->setTemplate('TaskManagement\NotifyMemebersForShareAssignment')->setVariables($params);
			$content = $renderer->render($viewModel);
				
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			
			$result = mail($params['emailAddress'], "O.R.A. - your contribution is required!", $content, $headers, 'orateam@ora.com');

			return $result;
		}
		
		return false;		
	}
}