# 关于
PHP-Curl轻量级的网络操作类，实现GET、POST、UPLOAD、DOWNLOAD常用操作，支持链式写法

# 需求
PHP 5.3 +

# 示例

#####GET:
```php
$this->curl->get(目标网址);
```


#####POST:
```php
$this->curl->post(变量名, 变量值)->post(多维数组)->submit(目标网址);
```


#####DOWNLOAD:
```php
$this->curl->download(文件地址)->save(保存路径);
```


#####UPLOAD:
```php
$this->curl->post(多维数组)->upload($_FILE字段, 本地路径, 文件类型, 原始名称)->submit(目标网址);
```


#####配置
参考:http://php.net/manual/en/function.curl-setopt.php
```php
$this->curl->set('CURLOPT_选项', 值)->post(多维数组)->submit(目标网址);
```

#####自动重试
```php
// 出错自动重试3次(默认不启用)
$this->curl->retry(3)->post(多维数组)->submit(目标网址);
```
