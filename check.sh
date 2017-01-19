#!/bin/bash

urls=(https://iwork.apc.gov.tw/HRF_WEB https://iwork.apc.gov.tw/HRB_WEB https://iwork.apc.gov.tw/JOB_COUNSELORS_WEB)

cnts=(47852 687 692)

for ((i=0; i<${#urls[@]}; i++)); do
    cnt=`wget ${urls[$i]} -O - --no-check-certificate |wc -c`
    if [ $cnt != ${cnts[$i]} ]; then
        /opt/lampp/bin/php /opt/lampp/htdocs/stockDB/mail.php ${urls[$i]} 'oh'
    else
        /opt/lampp/bin/php /opt/lampp/htdocs/stockDB/mail.php ${urls[$i]} 'ok'
    fi
done
