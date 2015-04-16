<?php

use Kanbanize\Service\KanbanizeAPIMock;
use Kanbanize\Service\KanbanizeServiceImpl;
use Kanbanize\KanbanizeTask;

return array(
		'service_manager' => array(
				'factories' => array(
						'Kanbanize\KanbanizeService' => function ($locator) {
							$api = new KanbanizeAPIMock([
								'114' => [
									'taskid'		=> 114,
									'columnname'	=> KanbanizeTask::COLUMN_ACCEPTED
								],
								'115' => [
									'taskid'		=> 115,
									'columnname'	=> KanbanizeTask::COLUMN_COMPLETED
								],
								'116' => [
									'taskid'		=> 116,
									'columnname'	=> KanbanizeTask::COLUMN_ONGOING
								],
								'117' => [
									'taskid'		=> 117,
									'columnname'	=> KanbanizeTask::COLUMN_COMPLETED
								],
								'118' => [
									'taskid'		=> 118,
									'columnname'	=> KanbanizeTask::COLUMN_ONGOING
								],
								'119' => [
									'taskid'		=> 119,
									'columnname'	=> KanbanizeTask::COLUMN_COMPLETED
								],
								'120' => [
									'taskid'		=> 120,
									'columnname'	=> KanbanizeTask::COLUMN_ACCEPTED
								]
							]);
							return new KanbanizeServiceImpl($api);
						},
				),
		),
);