<?php

namespace Ora\StreamManagement;

use Ora\ReadModel\Organization;

/**
 * @author Giannotti Fabio
 */
interface StreamService
{
	public function getStream($id);
	
	public function findStream($id);
	
} 