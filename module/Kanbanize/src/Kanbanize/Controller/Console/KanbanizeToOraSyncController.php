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
use AcMailer\Service\MailService;
use Kanbanize\Service\NotificationService;

/**
 * assumptions:
 * - one board is bound to one organization
 */
class KanbanizeToOraSyncController extends AbstractConsoleController {

    CONST API_URL_FORMAT = "https://%s.kanbanize.com/index.php/api/kanbanize";

    protected $taskService;

    protected $organizationService;

    protected $userService;

    protected $kanbanizeService;

    protected $mailService;

    public function __construct(
        TaskService $taskService,
        OrganizationService $organizationService,
        UserService $userService,
        KanbanizeService $kanbanizeService,
        NotificationService $mailService

    ) {
        $this->taskService = $taskService;
        $this->organizationService = $organizationService;
        $this->userService = $userService;
        $this->kanbanizeService = $kanbanizeService;
        $this->mailService = $mailService;
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

            if (!$stream || !$stream->isBoundToKanbanizeBoard()) {
                continue;
            }

            $kanbanize = $org->getSettings(Organization::KANBANIZE_SETTINGS);

            $this->kanbanizeService
                 ->initApi($kanbanize['apiKey'], $kanbanize['accountSubdomain']);

            $this->write("loading board activities stream {$stream->getId()} board {$stream->getBoardId()}");

            $kanbanizeTasks = $this->kanbanizeService
                                   ->getTasks($stream->getBoardId());

            //when something goes wrong a string is returned
            if (is_string($kanbanizeTasks)) {
                $this->write($kanbanizeTasks);
                continue;
            }

            $mapping = $kanbanize['boards'][$stream->getBoardId()]['columnMapping'];

            if ($this->isMappingChanged($stream->getBoardId(), $mapping)) {

                $this->sendAlertEmail($org);

                continue;
            }

            foreach($kanbanizeTasks as $kanbanizeTask) {
                $this->write("task {$kanbanizeTask['taskid']}");

                $task = $this->taskService
                             ->findTaskByKanbanizeId($kanbanizeTask['taskid']);

                if (!$task) {
                    $this->blockTaskOnKanbanize($kanbanizeTask);
                    continue;
                }

                $this->fixColumnOnKanbanize(
                    $task,
                    $kanbanizeTask,
                    $stream->getBoardId(),
                    $mapping
                );

                $this->updateTaskOnKanbanize(
                    $task,
                    $kanbanizeTask,
                    $stream
                );

                $this->updateTaskPositionFromKanbanize(
                    $task,
                    $kanbanizeTask,
                    $systemUser
                );
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

    /**
     * Checks if the columns of the kanban board differ from the current
     * column -> status mapping
     */
    private function isMappingChanged($boardId, $mapping)
    {
        $board = $this->kanbanizeService
                      ->getBoardStructure($boardId);

        if (!is_array($board)) {
            $this->write("  error retrieving board $boardId");

            return false;
        }

        $mappedColumns = array_keys($mapping);
        $kanbanizeColumns = array_column($board['columns'], 'lcname');
        array_pop($kanbanizeColumns); //removes temp archive column

        if ($mappedColumns == $kanbanizeColumns) {
            return false;
        }

        $this->write("  mapping changed");

        return true;
    }

    private function sendAlertEmail($organization)
    {
        $this->mailService
             ->sendKanbanizeSyncAlert($organization);
    }

    /**
     * first case: task on kanbanize but not on Welo
     * block task
     */
    private function blockTaskOnKanbanize($kanbanizeTask)
    {
        if ($kanbanizeTask['blocked']) {
            return;
        }

        $result = $this->kanbanizeService
                        ->blockTask(
                                $kanbanizeTask['boardparent'],
                                $kanbanizeTask['taskid'],
                                'task not on Welo'
        );

        $this->write("  try to block it: $result");
    }

    /**
     * move task to a column matching its status
     */
    private function fixColumnOnKanbanize($task, $kanbanizeTask, $boardId, $mapping)
    {
        if ($mapping[$kanbanizeTask['columnname']] == $task->getStatus()) {
            return;
        }

        $rightColumn = array_search($task->getStatus(), $mapping);

        try {
            $result = $this->kanbanizeService
                           ->moveTaskonKanbanize(
                                  $task,
                                  $rightColumn,
                                  $boardId
            );

        } catch (Exception $e) {

        } finally {
            $this->write("  try move it to '$rightColumn': $result");
        }
    }

    /**
     * update kanbanize task data based on O.R.A
     */
    private function updateTaskOnKanbanize($task, $kanbanizeTask, $stream)
    {
        if (!$task->isUpdatedBefore(new \DateTime($kanbanizeTask['updatedat']))) {
            return;
        }

        $result = $this->kanbanizeService
                       ->updateTask(
                             $task,
                             $kanbanizeTask,
                             $stream->getBoardId());

        $this->write("  try update it: $result");
    }

    private function updateTaskPositionFromKanbanize($task, $kanbanizeTask, $systemUser)
    {
        //position makes sense only for ongoing tasks
        if ($task->getStatus() != TaskInterface::STATUS_ONGOING) {
            return;
        }

        if ($kanbanizeTask['position'] == $task->getPosition()) {
            return;
        }

        $this->transaction()->begin();

        try {
            $taskAggregate = $this->taskService
                                  ->getTask($task->getId());

            $taskAggregate->setPosition($kanbanizeTask['position'], $systemUser);

            $this->transaction()->commit();

        } catch (Exception $e) {
            $this->transaction()->rollback();
        }
    }
}