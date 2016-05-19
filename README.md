# 关于
PHP-Curl是一个轻量级的网络操作类，实现GET、POST、UPLOAD、DOWNLOAD常用操作，支持链式写法

# 需求
对低版本做了向下支持，但建议使用 PHP 5.3 +

# 示例
```php
$curl = new Curl;
```
或者
```php
$curl = Curl::init();
```


#####GET:
```php
$curl->get(目标网址);
```


#####POST:
原生Curl是不支持POST多维数组的，本类使用post_fields_build方法实现了多维数组的提交
```php
$curl->post(变量名, 变量值)->post(多维数组)->submit(目标网址);
```

#####UPLOAD:
```php
$curl->post(多维数组)->upload($_FILE字段, 本地路径, 文件类型, 原始名称)->submit(目标网址);
```


#####DOWNLOAD:
```php
$curl->download(文件地址)->save(保存路径);
```


#####配置
参考:http://php.net/manual/en/function.curl-setopt.php
```php
$curl->set('CURLOPT_选项', 值)->post(多维数组)->submit(目标网址);
```

#####自动重试
```php
// 出错自动重试N次(默认0)
$curl->retry(3)->post(多维数组)->submit(目标网址);
```
