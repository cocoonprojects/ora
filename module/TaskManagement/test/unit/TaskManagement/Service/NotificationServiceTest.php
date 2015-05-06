<?php

namespace TaskManagement\Service;

use Zend\View\Renderer\PhpRenderer;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver\TemplateMapResolver;


class NotificationServiceTest extends \PHPUnit_Framework_TestCase {
	

	/**
	 * @var \Guzzle\Http\Client
	 */
	private $mailcatcher;
	/**
	 * @var NotificationService
	 */
	private $notificationService;
	
	protected function setUp() {
		
		$emailTemplates = array('TaskManagement\NotifyMemebersForShareAssignment' => __DIR__.'/../../../../view/task-management/email_templates/hurryup-taskmember.phtml');
		
		$this->notificationService = new NotificationService($emailTemplates);
		$this->mailcatcher = new \Guzzle\Http\Client('http://127.0.0.1:1080');
		$this->cleanEmailMessages();
		
	}
	
	
	
	public function testSendEmailNotificationForShareAssignment(){
		
		$params = array(
				'name' => 'Dorian Gray',
				'taskSubject' => 'new book',
				'taskId' => '11111ab-1111-1111-1111-11111111c500',
				'emailAddress' => 'doriangray@email.com',
				'url' => 'http://www.example.com'
		);

		$this->notificationService->sendEmailNotificationForShareAssignment($params);
		
		$emails = $this->getEmailMessages();
		
		$this->assertNotEmpty($emails);
		$this->assertEquals(1, count($emails));
		$this->assertEmailSubjectEquals('O.R.A. - your contribution is required!', $emails[0]);
		$this->assertEmailHtmlContains('new book', $emails[0]);
		$this->assertNotEmpty($emails[0]->recipients);
		$this->assertEquals($emails[0]->recipients[0], '<doriangray@email.com>');
		
	}
	
	
	
	protected function cleanEmailMessages()
	{
		$this->mailcatcher->delete('/messages')->send();
	}
	
	protected function getEmailMessages()
    {
        $jsonResponse = $this->mailcatcher->get('/messages')->send();
        return json_decode($jsonResponse->getBody());
    }
    
    public function getLastEmailMessage()
    {
    	$messages = $this->getEmailMessages();
    	if (empty($messages)) {
    		$this->fail("No messages received");
    	}
    	// messages are in descending order
    	return reset($messages);
    }
    
    public function assertEmailSubjectEquals($expected, $email, $description = '')
    {
    	$this->assertContains($expected, $email->subject, $description);
    }
    
    public function assertEmailHtmlContains($needle, $email, $description = '')
    {
    	$response = $this->mailcatcher->get("/messages/{$email->id}.html")->send();
    	$this->assertContains($needle, (string)$response->getBody(), $description);
    }
    
}