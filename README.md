# 自动删除日志工具
## 使用方法:
#### -time 5  				设置为日志仅保留5天,会按照文件修改时间,删除5天前的日志(默认为删除30天之前的日志)
#### -path /var/file/dir/   	设置要删除的目录,可以设置多个-path参数


### 示例:php auto_clear_log.php -time 5 -path /var/www/html/ 

默认删除的日志后缀名为.log
如果要添加日志文件后缀名 请在第20行添加

#### 请配合crontab使用 每小时自动查找并清除60天之前的日志 示范:
#### 1 * * * * /usr/local/bin/php /root/auto_clear_log.php -path /home/wwwroot/ -time 60

#### 顺便说一句,其实一条shell语句就能搞定 find /var/www/html/ -mtime +10 -name "*.log" -exec rm -rf {} \;