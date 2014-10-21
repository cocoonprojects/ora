#installazione di nodejs
if [ -f /usr/local/bin/node ]; then
 echo "NODE GIA' INSTALLATO."
else
 echo "INSTALLAZIONE DI NODE..."
 git clone https://github.com/joyent/node.git
 cd node
 git checkout v0.10.31 #Try checking nodejs.org for what the stable version is
 ./configure && make && make install
 cd ..
 echo "...FATTO"
fi

#installazione di npm
if [ -f /usr/local/bin/npm ]; then
 echo "NPM GIA' INSTALLATO."
else
 echo "INSTALLAZIONE DI NPM..."
 curl https://www.npmjs.org/install.sh | sh
 echo "...FATTO"
fi

#installazione di bower
if [ -f /usr/local/bin/bower ]; then
 echo "BOWER GIA' INSTALLATO."
else
 echo "INSTALLAZIONE DI BOWER..."
 npm install -g bower --allow-root
 echo "...FATTO"
fi