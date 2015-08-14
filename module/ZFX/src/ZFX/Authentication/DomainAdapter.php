<?php 

namespace ZFX\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Http\Request;
use Application\Service\UserService;
use Application\Entity\User;


class DomainAdapter implements AdapterInterface
{
	/**
	 * 
	 * @var string
	 */
	private $hostname;
	/**
	 * 
	 * @var Application\Service\UserService
	 */
	private $userService;
	
	private $domainUserMap = ['localhost' => User::SYSTEM_USER];
	
	public function __construct($hostname, UserService $userService){
		$this->hostname = $hostname;
		$this->userService = $userService;
	}
	
	public function authenticate(){
		
		$userId = $this->findUserIdFromHostname($this->hostname);
		
		if(is_null($userId)){
			
			return new Result(Result::FAILURE, 'Cannot find any users');
		}
		
		if(!is_null($userId)){
			$user = $this->userService->findUser($userId);
			
			if($user instanceof User){
				return new Result(Result::SUCCESS, $user);
			}
		}
		
		return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, 'User not found based on hostname');
	}
	
	
	public function findUserIdFromHostname($hostname){
	
		if(key_exists($hostname, $this->domainUserMap)){
			return $this->domainUserMap[$hostname];
		}
	
		return null;
	}
	
	
}