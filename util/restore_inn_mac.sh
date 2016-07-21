#!/bin/sh

source ~/.bashrc
export XAMPPFILES=/Applications/XAMPP/xamppfiles

echo 'USE stockDB; DROP DATABASE stockDB;' | mysql -u root -p
sudo cp $XAMPPFILES/etc/my_inn.cnf $XAMPPFILES/etc/my.cnf
sudo $XAMPPFILES/mysql/scripts/ctl.sh stop
sudo $XAMPPFILES/mysql/scripts/ctl.sh start
date
echo 'CREATE DATABASE stockDB; USE stockDB; source stockDB.inn.sql;' | mysql -u root -p
date
