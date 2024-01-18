易共享
====================

### 写在前面
    - 一个简单的文件共享项目.
	- 安装简单, 使用简单, 无权限限制. 
	
### 安装
1. 运行环境
```php
    1. PHP 5.4 或更新的版本.
	2. nginx-1.8.0 或更新的版本.
```

2. 修改上传限制
```php
    Nginx :
        client_max_body_size 500M;
    php.ini :
        post_max_size = 500M
        upload_max_filesize = 500M
```