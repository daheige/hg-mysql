# awk用法
        awk '{printf("%s\n",$0)}' awk_test.md #$0表示整个内容,$1表示第一列,$2第二列,以此类推(循环)

# awk查看show status;
        mysqladmin -uroot -p1234 extended-status | awk '/Queries/{printf("%d ",$4)}'
        mysqladmin -uroot -p1234 extended-status | awk '/Queries/{printf("%d ",$4)}/Threads_connected/{printf("%d ",$4)}/Threads_running/{printf("%d\n",$4)}';
        199 1 1 #打印结果
        
        mysqladmin -uroot -p1234 extended-status | awk '/Queries/{q=$4}/Threads_connected/{c=$4}/Threads_running/{r=$4}END{printf("q=%d c=%d r=%d \n",q,c,r)}';
        q=349 c=1 r=1 #打印执行结果 q表示查询数,c表示连接数,r正在运行数(进程)
        


