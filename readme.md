# this is my study for thinkphp 
## docker 搭建 php 环境
### 安装 docker docker-compose
    略
### 拉取官方镜像
    docker pull nginx:latest
    docker pull php:7.1.6-fpm
### 搭建 web 环境
建立相关配置文件夹及配置文件
    
    ./docker-conf/nginx
    ./docker-conf/php
    ./docker-conf/mysql

* [docker-compose.yml](docker-compose.yml)