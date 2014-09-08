#!/bin/bash

echo "Installazione dell'ultima versione di Doctrine"
pear channel-discover pear.doctrine-project.org
pear channel-discover pear.symfony-project.com
pear channel-discover pear.symfony.com
pear install doctrine/DoctrineORM
echo "Fine installazione di Doctrine"