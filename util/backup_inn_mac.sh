#!/bin/sh

source ~/.bashrc
mysqldump -u root -p stockDB > stockDB.inn.sql
