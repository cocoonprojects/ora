<?php

namespace People\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class TaskStatsJsonModel extends JsonModel{

	public function serialize(){

		$resource = $this->getVariable("resource");

		$hal['membershipsCount'] = isset($resource['membershipsCount']) ? $resource['membershipsCount'] : '';
		$hal['ownershipsCount'] = isset($resource['ownershipsCount']) ? $resource['ownershipsCount'] : '';
		$hal['creditsCount'] = isset($resource['creditsCount']) ? $resource['creditsCount'] : '';
		$hal['averageDelta'] = isset($resource['averageDelta']) ? $resource['averageDelta'] : '';

		return Json::encode($hal);
	}
}