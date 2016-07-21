#!/bin/sh

source ~/.bashrc
export XAMPPFILES=/opt/lampp
export PATH=$PATH:$XAMPPFILES/bin

echo 'USE stockDB; DROP DATABASE stockDB;' | mysql -u root -p
sudo cp $XAMPPFILES/etc/my_mem.cnf $XAMPPFILES/etc/my.cnf
sudo $XAMPPFILES/mysql/scripts/ctl.sh stop
sudo $XAMPPFILES/mysql/scripts/ctl.sh start
date
echo 'CREATE DATABASE stockDB; USE stockDB; source stockDB.mem.sql;' | mysql -u root -p
date
