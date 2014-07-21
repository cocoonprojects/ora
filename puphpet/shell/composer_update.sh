#!/bin/bash

echo "Esecuzione di composer.phar update sul progetto..."
cd /vagrant
php composer.phar update

cd /vagrant/src
php composer.phar update

echo "...FATTO"
