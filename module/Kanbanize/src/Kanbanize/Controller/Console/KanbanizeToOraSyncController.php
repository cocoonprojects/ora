<?php

namespace Kanbanize\Controller\Console;

use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Console\Request as ConsoleRequest;

use Application\Entity\User;
use Application\Service\UserService;
use People\Service\OrganizationService;
use People\Organization;
use TaskManagement\Service\TaskService;
use TaskManagement\TaskInterface;
use Kanbanize\Service\KanbanizeService;

class KanbanizeToOraSyncController extends AbstractConsoleController {

    CONST API_URL_FORMAT = "https://%s.kanbanize.com/index.php/api/kanbanize";

    protected $taskService;

    protected $organizationService;

    protected $userService;

    protected $kanbanizeService;

    public function __construct(
        TaskService $taskService,
        OrganizationService $organizationService,
        UserService $userService,
        KanbanizeService $kanbanizeService

    ) {
        $this->taskService = $taskService;
        $this->organizationService = $organizationService;
        $this->userService = $userService;
        $this->kanbanizeService = $kanbanizeService;
    }

    public function syncAction()
    {
        $request = $this->getRequest();
        $this->assertIsConsoleRequest($request);

        $systemUser = $this->userService
                           ->findUser(User::SYSTEM_USER);

        $this->assertIsSystemUser($systemUser);

        $orgs = $this->organizationService
                     ->findOrganizations();

        foreach($orgs as $org) {
            $this->write("org {$org->getName()} ({$org->getId()})");

            $stream = $this->kanbanizeService
                           ->findStreamByOrganization($org);

            if (!$stream) {
                continue;
            }

            if (!$stream->isBoundToKanbanizeBoard()) {
                continue;
            }

            $kanbanize = $org->getSettings(Organization::KANBANIZE_SETTINGS);

            $this->kanbanizeService->initApi($kanbanize['apiKey'], $kanbanize['accountSubdomain']);

            $this->write("loading board activities stream {$stream->getId()} board {$stream->getBoardId()}");

            $kanbanizeTasks = $this->kanbanizeService
                                      ->getTasks($stream->getBoardId());

            //when something goes wrong a string is returned
            if (is_string($kanbanizeTasks)) {
                $this->write($kanbanizeTasks);
            }

            foreach($kanbanizeTasks as $kanbanizeTask) {
                $this->write("task {$kanbanizeTask['taskid']}");

                $task = $this->taskService
                             ->findTaskByKanbanizeId($kanbanizeTask['taskid']);

                // first case: task on kanbanize but not on O.R.A
                // block task
                if (!$task && !$kanbanizeTask['blocked']) {
                    $result = $this->kanbanizeService
                                    ->blockTask(
                                            $kanbanizeTask['boardparent'],
                                            $kanbanizeTask['taskid'],
                                            'task not on O.R.A'
                        );

                    $this->write("  try to block it: $result");
                }

                // update kanbanize task based on O.R.A
                // if ($task->updatedat < $kanbanizeTask['updatedat']) {

                //     syncTask

                // }
            }

            $this->write("");
        }
    }

    private function write($msg)
    {
        $now = (new \DateTime('now'))->format('Y-m-d H:s');

        echo "[$now] ", $msg, "\n";
    }

    private function assertIsConsoleRequest($request)
    {
        if (!$request instanceof ConsoleRequest) {
            $this->write("use only from a console!");

            exit(1);
        }
    }

    private function assertIsSystemUser($systemUser)
    {
        if (!$systemUser) {
            $this->write("missing system user, aborting");

            exit(1);
        }

        $this->write("loaded system user {$systemUser->getFirstname()}");
    }
}