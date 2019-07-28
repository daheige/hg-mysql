# innodb三大特性
    插入缓存 change buffer
    两次写： double write
    自适应哈希： adaptive hash index
# 插入缓存
    插入缓存是把普通索引上的dml操作，从随机i/o变成顺序i/o,提高i/o效率
    工作原理，先判断插入的普通索引页，是否在缓冲池中，如果在就直接插入，如果不在先放入change buffer中
    然后进行change buffer和普通索引的合并操作，可以将多个插入合并到一个操作中，提高了普通索引的插入性能。
# 两次写
    保证写入的安全性，防止mysql实例宕机，innodb发生数据页部分写的问题。如果发生了宕机，先通过副本把原来的页还原出来，再通过redo log进行重放恢复。
    双写缓冲是一个位于系统表空间中的存储区域，innodb缓冲池中刷出的脏页在被写入数据文件之前，都会先写入double write buffer中，然后分两次写入缓冲区，每次1MB大小的数据写入磁盘共享空间，最后再从double write buffer把数据写入数据文件中。
    虽然数据发生了两次写入，但是不消耗2倍的i/o消耗，数据写入缓冲后，本身是一个大型的连续块，会通过一次fsync通知操作系统负责写入文件中。
# 自适应哈希索引
    mysql> show variables like "%hash_index%";
    +----------------------------------+-------+
    | Variable_name                    | Value |
    +----------------------------------+-------+
    | innodb_adaptive_hash_index       | ON    |
    | innodb_adaptive_hash_index_parts | 8     |
    +----------------------------------+-------+
    可以调大分区值，提高并发性。
