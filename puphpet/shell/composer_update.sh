#!/bin/bash

cd /vagrant

echo "Esecuzione di composer self-update..."
composer self-update
echo "...FATTO"

echo "Esecuzione di composer update sul progetto..."
composer update
echo "...FATTO"
