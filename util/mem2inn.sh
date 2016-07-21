#!/bin/sh

cat stockDB.mem.sql | sed 's/MEMORY/InnoDB/' > stockDB.inn.sql
