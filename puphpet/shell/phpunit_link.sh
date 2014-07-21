#!/bin/bash

if [ -f /usr/bin/phpunit ]; then
	echo "link phpunit gia' presente."
else
	echo "link per phpunit"
	ln -s  /vagrant/vendor/phpunit/phpunit/phpunit /usr/bin/
	echo "...FATTO"
fi

