<?php

use Idephix\Idephix;

$targets = array(
    'stage-ora' => array(
        'hosts' => array('10.250.2.44'),
        'ssh_params' => ['user' => 'cocoon'],
        'deploy' => array(
            'local_base_dir' => __DIR__,
            'remote_base_dir' => "/var/www/vhosts/cocoon/",
            'rsync_exclude_file' => 'deploy_exclude'
        ),
    ),
    'stage-welo' => array(
        'hosts' => array('10.250.2.44'),
        'ssh_params' => ['user' => 'cocoon'],
        'deploy' => array(
            'local_base_dir' => __DIR__,
            'remote_base_dir' => "/var/www/vhosts/welo/",
            'rsync_exclude_file' => 'deploy_exclude'
        ),
    ),
);

$idx = new Idephix($targets);

$deploy = function($go = false) use ($idx)
{
    $target = $idx->getCurrentTarget();

    if ($target === null) {
        throw new \InvalidArgumentException(
            "Please provide a valid target with --env=<target>"
        );
    }

    $host = $idx->getCurrentTargetHost();
    $user = $target->get('ssh_params.user');
    $path = $target->get('deploy.remote_base_dir');
    $opts = '-rlDcz --no-perms --force --delete --progress';
    $opts .= ' --exclude-from=' . $target->get('deploy.rsync_exclude_file');

    $dryrun = $go ? '' : '--dry-run';

    $idx->local("rsync $opts $dryrun -e 'ssh' . $user@$host:$path");

    if ($go) {
        $idx->remote("cd $path && composer install --no-dev -o");
    }
};

$idx->add('deploy', $deploy);
$idx->run();