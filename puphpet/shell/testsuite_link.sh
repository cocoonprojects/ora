#!/bin/bash


if [ -f /usr/bin/phpunit ]; then
 echo "link phpunit gia' presente."
else
 echo "link per phpunit"
 ln -s /vagrant/vendor/phpunit/phpunit/phpunit /usr/bin/
 echo "...FATTO"
fi

if [ -f /usr/bin/behat ]; then
 echo "link behat gia' presente."
else
 echo "link per behat"
 ln -s /vagrant/vendor/behat/behat/bin/behat /usr/bin/
 echo "...FATTO"
fi

