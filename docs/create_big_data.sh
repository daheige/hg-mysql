#!/bin/bash

function makeRnd(){
    min=$1
    max=$(($2-$min+1))
    num=$(($RANDOM+1000000000)) #增加一个10位的数再求余
    echo $(($num%$max+$min))
}

echo "" > big_data_create.sql
echo "use test;" >> big_data_create.sql
echo "drop table big_data;" >> big_data_create.sql
echo "create table big_data(id bigint unsigned not null primary key auto_increment,name varchar(32) not null,age tinyint unsigned not null default 0) engine=innodb default charset utf8;" >> big_data_create.sql

#产生100w数据
count=1
while [ $count -le 1000000 ];do
     x=$count
     age=$(makeRnd 20 100)
     echo "insert into big_data(name,age) values(\"daheige_$x\",$age);" >> big_data_create.sql
     let count++
done

echo "finished"
exit 0

