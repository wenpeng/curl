# 关于
PHP-Curl是一个轻量级的网络操作类，实现GET、POST、UPLOAD、DOWNLOAD常用操作，支持方法链写法


# 需求
对低版本做了向下支持，但建议使用 PHP 5.3 +

# 安装
```shell
composer require wenpeng/curl
```
```php
use Wenpeng\Curl\Curl;
```


# 示例
```php
$curl = new Curl;
```
或者
```php
$curl = Curl::init();
```


##### GET:
```php
$curl->url(目标网址);
```


##### POST:
```php
$curl->post(变量名, 变量值)->post(多维数组)->url(目标网址);
```


##### UPLOAD:
```php
$curl->post(多维数组)->file($_FILE字段, 本地路径, 文件类型, 原始名称)->url(目标网址);
```


##### DOWNLOAD:
```php
$curl->url(文件地址)->save(保存路径);
```


##### 配置
参考:http://php.net/manual/en/function.curl-setopt.php

```php
$curl->set('CURLOPT_选项', 值)->post(多维数组)->url(目标网址);
```

##### 自动重试
```php
// 出错自动重试N次(默认0)
$curl->retry(3)->post(多维数组)->url(目标网址);
```

##### 结果
```php
// 任务结果状态
if ($curl->error()) {
    echo $curl->message();
} else {
    // 任务进程信息
    $info = $curl->info();
    
    // 任务结果内容
    $content = $curl->data();
}

```
