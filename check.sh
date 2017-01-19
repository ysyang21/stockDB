#!/bin/bash

urls=(https://iwork.apc.gov.tw/HRF_WEB https://iwork.apc.gov.tw/HRB_WEB https://iwork.apc.gov.tw/JOB_COUNSELORS_WEB)

cnts=(47852 687 692)

for ((i=0; i<${#urls[@]}; i++)); do
    cnt=`wget ${urls[$i]} -O - --no-check-certificate |wc -c`
    if [ $cnt != ${cnts[$i]} ]; then
        ../../bin/php mail.php ${urls[$i]} 'down'
    else
        ../../bin/php mail.php ${urls[$i]} 'fine'
    fi
done
