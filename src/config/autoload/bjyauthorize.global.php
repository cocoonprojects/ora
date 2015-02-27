<?php 
use Ora\User\User;

return array(
 
    'bjyauthorize' => array( 
         
        'identity_provider' => 'CurrentUserProvider',

        'role_providers' => array(
            'BjyAuthorize\Provider\Role\Config' => User::getRoleCollection(), 
        ),
    ),	
)
?>
