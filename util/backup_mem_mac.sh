#!/bin/sh

source ~/.bashrc
now=`date +"%Y%m%d"`
mysqldump -u root -p stockDB > stockDB.mem.sql
cp stockDB.mem.sql stockDB.mem.sql.$now
