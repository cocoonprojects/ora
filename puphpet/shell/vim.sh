#!/bin/bash

sudo apt-get install -y libncurses5-dev \
python-dev ruby-dev

cd /tmp
rm -rf vim74
wget ftp://ftp.vim.org/pub/vim/unix/vim-7.4.tar.bz2
tar xvjf vim-7.4.tar.bz2
cd vim74

./configure --with-features=huge \
            --enable-cscope \
            --without-x \
            --disable-gui \
            --prefix=/usr \
            --enable-luainterp \
            --with-lua-prefix=/usr \
            --enable-pythoninterp \
            --enable-rubyinterp

make VIMRUNTIMEDIR=/usr/share/vim/vim74
sudo make install