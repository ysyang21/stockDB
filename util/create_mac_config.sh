#!/bin/sh

source ~/.bashrc
export XAMPPFILES=/Applications/XAMPP/xamppfiles

sudo cp $XAMPPFILES/etc/my.cnf $XAMPPFILES/etc/my_inn.cnf
sudo cat $XAMPPFILES/etc/my_inn.cnf | sed 's:skip-external-locking:skip-external-locking\
default-storage-engine=MEMORY\
max_heap_table_size=512M:' > $XAMPPFILES/etc/my_mem.cnf
