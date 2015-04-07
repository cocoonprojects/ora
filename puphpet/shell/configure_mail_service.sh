#!/bin/bash

echo "INIZIO CONFIGURAZIONE DEL SERVIZIO DI INVIO EMAIL..."

sudo apt-get install -y sendmail
sudo apt-get install -y mailutils

if grep -Fxq "# Google nameservers" /etc/resolvconf/resolv.conf.d/head 
then
	sudo resolvconf -u 	
else
	echo '#' | sudo tee -a /etc/resolvconf/resolv.conf.d/head
	echo '#' | sudo tee -a /etc/resolvconf/resolv.conf.d/head
	echo '# Google nameservers' | sudo tee -a /etc/resolvconf/resolv.conf.d/head
	echo 'nameserver 8.8.4.4' | sudo tee -a /etc/resolvconf/resolv.conf.d/head
	echo '#' | sudo tee -a /etc/resolvconf/resolv.conf.d/head
	echo '#' | sudo tee -a /etc/resolvconf/resolv.conf.d/head
	
	sudo resolvconf -u 

fi

HOSTNAME=$(hostname)
if grep -Fxq "127.0.0.1 $HOSTNAME localhost.localdomain" /etc/hosts
then
	echo "...hostname gia' configurato..."
else
	echo "127.0.0.1 $HOSTNAME localhost.localdomain" | sudo tee -a /etc/hosts
	
	echo "...hostname configurato correttamente..."
fi

echo "...FATTO!"
