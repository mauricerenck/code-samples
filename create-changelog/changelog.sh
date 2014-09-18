#!/bin/bash

function jsonval {
    temp=`echo $json | sed 's/\\\\\//\//g' | sed 's/[{}]//g' | awk -v k="text" '{n=split($0,a,","); for (i=1; i<=n; i++) print a[i]}' | sed 's/\"\:\"/\|/g' | sed 's/[\,]/ /g' | sed 's/\"//g' | grep -w $prop | cut -d":" -f2| sed -e 's/^ *//g' -e 's/ *$//g'`
    echo ${temp##*|}
}
 
lastDate=`ls -l changelog.txt | awk '{print $6,  $7}'`
json=`cat package.json`
prop='version'
appversion=`jsonval`

echo "MyCoolTool ($appversion) UNRELEASED; urgency=low\n" > changelog.txt
git log --since="$lastDate" --format="  * %s" --no-merges --grep=#resolve --grep=#close >> changelog.txt
echo "\n" >> changelog.txt
git log --format='-- %aN' | sort -u  >> changelog.txt
exit