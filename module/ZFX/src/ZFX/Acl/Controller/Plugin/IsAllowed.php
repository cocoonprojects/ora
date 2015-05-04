<?php
namespace ZFX\Acl\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Permissions\Acl\Acl;

/**
 * IsAllowed Controller plugin. Allows checking access to a resource/privilege in controllers.
 *
 */
class IsAllowed extends AbstractPlugin
{
	/**
	 * @var Acl
	 */
	protected $acl;

	/**
	 * @param Acl $acl
	 */
	public function __construct(Acl $acl)
	{
		$this->acl = $acl;
	}

	/**
	 * @param mixed	  $resource
	 * @param mixed|null $privilege
	 *
	 * @return bool
	 */
	public function __invoke($user, $resource, $privilege = null)
	{
		return $this->acl->isAllowed($user, $resource, $privilege);
	}
}
