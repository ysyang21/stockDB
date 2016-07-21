#!/bin/sh

cat stockDB.inn.sql | sed 's/InnoDB/MEMORY/' > stockDB.mem.sql
