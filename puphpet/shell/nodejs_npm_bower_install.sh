#installazione di nodejs
git clone https://github.com/joyent/node.git
cd node
git checkout v0.10.31 #Try checking nodejs.org for what the stable version is
./configure && make && make install
cd ..

#installazione di npm
curl https://www.npmjs.org/install.sh | sh


#installazione di bower
npm install -g bower --allow-root

