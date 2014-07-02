#!/bin/bash

if [ -f /etc/apache2/sites-available/oraproject.conf ]; then
        echo "Virtualhost di progetto gia' presente."
    else
	echo "Copia del virtualhost di progetto in corso..."
        cp /vagrant/puphpet/shell/oraproject.conf /etc/apache2/sites-available/
        ln -s /etc/apache2/sites-available/oraproject.conf /etc/apache2/sites-enabled
	echo "Virtualhost copiato; riavvio del server apache"
	apache2ctl restart
	echo "Virtualhost configurato correttamente"
fi
