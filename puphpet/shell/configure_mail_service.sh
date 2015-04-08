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
	echo 'nameserver 8.8.8.8' | sudo tee -a /etc/resolvconf/resolv.conf.d/head
	echo '#' | sudo tee -a /etc/resolvconf/resolv.conf.d/head
	echo '#' | sudo tee -a /etc/resolvconf/resolv.conf.d/head
	
	sudo resolvconf -u 

fi


#con il nuovo nameserver dobbiamo modificare il file /etc/hosts sia per velocizzare l'invio delle email sia per rendere raggiungibile l'host 'oraprojecttest' durante l'esecuzione dei test di accettazione

HOSTNAME=$(hostname)
if grep -Fxq "127.0.0.1 $HOSTNAME localhost.localdomain" /etc/hosts
then
	echo "...hostname $HOSTNAME gia' configurato..."
else
	echo "127.0.0.1 $HOSTNAME localhost.localdomain" | sudo tee -a /etc/hosts
	
	echo "...hostname $HOSTNAME configurato correttamente..."
fi

HOSTNAME_TEST='oraprojecttest'
if grep -Fxq "192.168.56.111 $HOSTNAME_TEST localhost.localdomain" /etc/hosts
then
	echo "...hostname $HOSTNAME_TEST gia' configurato..."
else
	echo "192.168.56.111 $HOSTNAME_TEST localhost.localdomain" | sudo tee -a /etc/hosts
	
	echo "...hostname $HOSTNAME_TEST configurato correttamente..."
fi

echo "...FATTO!"
