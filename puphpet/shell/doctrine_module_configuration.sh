echo "INIZIO CONFIGURAZIONE DEL MODULO DI DOCTRINE PER ZF2"

if [ -f /vagrant/config/application.config.php ]; then
        echo "link simbolico gia' presente."
    else
	mkdir /vagrant/config
	ln -s /vagrant/src/config/application.config.php /vagrant/config/
	echo "....fatto!"
fi
