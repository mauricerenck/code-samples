#!/bin/bash

cd /var/www/
if [ ! -d "$1" ]; then
    mkdir -p "$1/logs"
    chown YOURUSERNAME:www-data $1-$2
    chmod -R 775 $1
    cd /var/www/DEPLOYSCRIPTDIRECTORX/
    head -n -2 deploy.json > temp.txt ; mv temp.txt deploy.json
    printf "\t},\n\t\"$1\": {\n\t\t\"path\":\t\t\"/var/www/$1\",\n\t\t\"limit\":\t\"master\"\n\t}\n}" >> deploy.json
else
    echo Project already exists!
fi
exit
