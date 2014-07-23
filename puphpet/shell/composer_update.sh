#!/bin/bash

cd /vagrant

echo "Esecuzione di composer.phar self-update..."
php composer.phar self-update
echo "...FATTO"

echo "Esecuzione di composer.phar update sul progetto..."
php composer.phar update
echo "...FATTO"
