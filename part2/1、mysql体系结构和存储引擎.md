# mysql体系和存储引擎
    mysql体系分为 mysql server层和存储引擎
# mysql server体系
    连接层和sql层
    对于sql层包含权限判断，查询缓存，解析器，预处理，查询优化器，缓存和执行计划
    mysql server通过存储引擎访问数据库的数据。
# 查看查询缓存
    mysql> show variables like "%query_cache_type%";
    +------------------+-------+
    | Variable_name    | Value |
    +------------------+-------+
    | query_cache_type | OFF   |
    +------------------+-------+
    1 row in set (0.00 sec)
    关闭查询缓存
    $ sudo vim /etc/mysql/mysql.conf.d/mysqld.cnf
    #关闭查询缓存
    query_cache_size        = 0
    query_cache_type        = 0

# 存储引擎
    常见的存储引擎
    innodb                                  myisam
    支持事务，行锁，支持mvcc多版本              不支持事务，表锁，低并发，数据文件的拓展名.MYD
    并发性高，数据和索引文件存在.Ibd文件中        索引的文件拓展名.MYI
    并且缓存在内存中                           只缓存索引文件，不缓存数据文件
    count(*)需要扫描全表，统计行数              直接从计数器中读取保存好的行数
# innodb体系
    数据库和数据库实例
    mysql数据库是一个单进程多线程模型的数据库
    数据库实例就是进程加内存的组合
    innodb体系实际上由三部分组成： 内存结构，线程，磁盘文件
# innodb存储体系
    innodb逻辑存储单元，分为4部分，表空间，段，区，页组成
    层级关系  表空间--->段-->区--->页
    
    表空间分为系统表空间，独立表空间，共享表空间。
    系统表空间在数据库安装的时候就初始化了，创建一个ibdata1文件，存储所有的数据信息和回滚段undo的信息
    在5.6之后，undo表空间，可以单独配置。
    在生产环境，一般建议将系统空间表，调整为1GB。
    mysql> show variables like "%innodb_data%"
    -> ;
    +-----------------------+------------------------+
    | Variable_name         | Value                  |
    +-----------------------+------------------------+
    | innodb_data_file_path | ibdata1:12M:autoextend |
    | innodb_data_home_dir  |                        |
    +-----------------------+------------------------+
    2 rows in set (0.00 sec)

    除了系统表空间，还有独立表空间
    查看是否开启独立表空间设置 innodb_file_per_table
    mysql> show variables like "%innodb_file%";
    +--------------------------+-----------+
    | Variable_name            | Value     |
    +--------------------------+-----------+
    | innodb_file_format       | Barracuda |
    | innodb_file_format_check | ON        |
    | innodb_file_format_max   | Barracuda |
    | innodb_file_per_table    | ON        |
    +--------------------------+-----------+

    目前mysql版本中，默认使用独立表空间文件，就是每个表都有自己的表空间文件，不同系统表结构文件
    独立表空间文件存储表的B+tree树数据，索引和插入缓冲系统，其余信息放在系统表空间文件中。
# 共享表空间和独立表空间区别
    共享表空间的数据和文件都在一起，但是无法在线回收空间，共享表空间想要收回，必须将innodb表数据进行备份
    然后删除原有的表，然后把数据导入到原表结构一样的表中，对于统计分析业务，不适合用共享表空间。
    综合考虑，独立表空间的效率和性能比共享表空间高。

# mysql的临时表空间
    mysql5.7把临时表的数据从系统表空间中抽离出来，形成自己的独立表空间参
    innodb_temp_data_file_path
    但临时表的相关检索信息放在系统表的information_schema库下的innodb_temp_table_info表中
    mysql> show variables like "%temp%";
    +----------------------------+-----------------------+
    | Variable_name              | Value                 |
    +----------------------------+-----------------------+
    | avoid_temporal_upgrade     | OFF                   |
    | innodb_temp_data_file_path | ibtmp1:12M:autoextend |
    | show_old_temporals         | OFF                   |
    +----------------------------+-----------------------+
# 通用表空间
    多个表放在同一个表空间中，可根据活跃度来划分表，存放在不同的磁盘上，可以减少metadata的存储开销，目前
    在生产环境中很少用。
# 段
    表空间由段组成，可以把一个表理解为一个段，通常有数据段，回滚段，索引段等。对于每个段由N个区和32个零散的页组成，段空间拓展以区进行。通常情况下，创建一个索引，就会创建两个段，分别是非叶子节点和叶子节点
    对于一个表有几个段呢？4个，是索引个数的2倍。
# 区
    区由连续的页组成，是物理上联系分配的一段空间，每个区的大小固定为1MB
# 页
    innodb的最小物理存储单元是page,一个区由64个连续的页组成，每个页默认是16kb
    一个页默认预留1/16的空间用于更新数据。一个页最少可以存放两行数据，虚拟最小行和虚拟最大行，用来限定记录范围，用来保证B+tree节点是双向链表结构。
    mysql> show variables like "%page_size%";
    +------------------+-------+
    | Variable_name    | Value |
    +------------------+-------+
    | innodb_page_size | 16384 |
    | large_page_size  | 0     |
    +------------------+-------+
    2 rows in set (0.00 sec)

    每一个页中保存了行记录，innodb存储引擎是面向行的，也就是数据是按照行存储的。
    在每个页中，具体有如下结构：
    file header         文件头，记录了页头的一些信息，checksum值，pervious page,next page的记录
    page header         记录了页的状态信息和存储信息，首个记录的position
    infimum+supremum    innodb每个数据页有两个虚拟行记录，用来限定记录边界
    Row records         行记录，实际对于表的行数据信息
    free space          空闲空间，同样是链表结构，当一个数据记录删除后，就会加入到空间链表中
    page directory      存放了记录的相对位置
    file trailer        innodb利用来保证页完整写入磁盘
# 行
    innodb存储引擎面向行的，数据按照行存储。对于innodb存储有两种文件格式，一种叫antelope,一种是
    barracuda.
    行记录在生产环境一般用dynamic形式，当一个页的数据放不下的时候，就会出现行溢出，进行拆分。对于大型的
    text/blob格式，数据页只存前20个字节的指针，dynamic实际采用的数据都存放在溢出的页中(off-page)
# 内存结构
    分为系统全局区SGA,程序缓存区PGA
    系统全局区：
        innodb_buffer_pool 用来缓存innodb表的数据，索引，插入缓冲，数据字典等信息
        innodb_log_buffer: 事务在内存中的缓冲，就是redo log buffer大小
        query_cache: 查询缓存
    程序缓存区：
        sort_buffer_size:  主要用于sql语句在内存中的临时排序
        join_buffer_size: 表连接使用
        read_buffer_size: 表顺序扫描的缓存，只能用于myisam
        read_rnd_buffer_size: mysql随机读取缓冲区大小
        
        两个特殊的，建议生产环境，设置一样大小
        tmp_table_size: 临时表空间，一般是没有用到索引使用的临时空间
        max_heap_table_size: 管理heap,memory存储引擎表
        
