# PHP-Curl
PHP-Curl轻量级的网络操作类，实现GET、POST、UPLOAD、DOWNLOAD常用操作，支持链式写法


- [x] GET:
```php
$this->curl->get(目标网址);
```


- [x] POST:
```php
$this->curl->post(变量名, 变量值)->post(多维数组)->submit(目标网址);
```

- [x] DOWNLOAD:
```php
$this->curl->download(文件地址)->save(保存路径);
```


- [x] UPLOAD:
```php
$this->curl->upload($_FILE字段, 本地路径, 文件类型, 原始名称)->submit(目标网址);
OR
$this->curl->post(多维数组)->upload($_FILE字段, 本地路径, 文件类型, 原始名称)->submit(目标网址);
```
