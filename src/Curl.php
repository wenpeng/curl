<?php
/**
 * Author:  Wenpeng
 * Email:   imwwp@outlook.com
 * Version: 1.0.0
 *
 * https://github.com/wenpeng/curl
 * 一个轻量级的网络操作类，实现GET、POST、UPLOAD、DOWNLOAD常用操作，支持链式写法。
 */

namespace Wenpeng;
 
class Curl {
    private $post;
    private $retry = 0;
    private $custom = array();
    private $option = array(
        'CURLOPT_HEADER'         => 0,
        'CURLOPT_TIMEOUT'        => 30,
        'CURLOPT_ENCODING'       => '',
        'CURLOPT_IPRESOLVE'      => 1,
        'CURLOPT_RETURNTRANSFER' => true,
        'CURLOPT_SSL_VERIFYPEER' => false,
        'CURLOPT_CONNECTTIMEOUT' => 10,
    );
    private $download = false;

    private $info;
    private $data;
    private $fail;
    private $message;

    private static $instance;
        
    /**
     * 静态实例化
     * @return self
     */
    public static function init()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * 任务进程信息
     *
     * @return array
     */
    public function info()
    {
        return $this->info;
    }

    /**
     * 任务结果内容
     *
     * @return string
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * 任务进程状态
     *
     * @return boolean
     */
    public function fail()
    {
        return $this->fail;
    }

    /**
     * 任务进程消息
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * 设置POST信息
     * @param array|string  $data
     * @param null|string   $value
     * @return self
     */
    public function post($data, $value = null)
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $this->post[$key] = $val;
            }
        } else {
            if ($value === null) {
                $this->post = $data;
            } else {
                $this->post[$data] = $value;
            }
        }
        return $this;
    }

    /**
     * 设置文件上传
     * @param string $field
     * @param string $path
     * @param string $type
     * @param string $name
     * @return self
     */
    public function file($field, $path, $type, $name)
    {
        $name = basename($name);
        if (class_exists('CURLFile')) {
            $this->set('CURLOPT_SAFE_UPLOAD', true);
            $file = curl_file_create($path, $type, $name);
        } else {
            $file = "@{$path};type={$type};filename={$name}";
        }
        return $this->post($field, $file);
    }

    /**
     * 提交GET请求
     * @param string $url
     */
    public function get($url)
    {
        $this->set('CURLOPT_URL', $url)->execute();
    }

    /**
     * 提交POST请求
     * @param string $url
     */
    public function submit($url)
    {
        $this->set('CURLOPT_URL', $url)->execute();
    }

    /**
     * 设置文件下载
     * @param string $url
     * @param string $path
     */
    public function download($url, $path)
    {
        $this->download = true;
        $this->set('CURLOPT_URL', $url)->execute();
        if (! $this->fail) {
            $fp = @fopen($path, 'w');
            if ($fp === false) {
                $this->fail = true;
                $this->message = $path . '不可写';
            } else {
                fwrite($fp, $this->data);
                fclose($fp);
            }
        }
    }

    /**
     * 配置Curl操作
     * @param array|string  $item
     * @param null|string   $value
     * @return self
     */
    public function set($item, $value = null)
    {
        if (is_array($item)) {
            foreach($item as $key => $val){
                $this->custom[$key] = $val;
            }
        } else {
            $this->custom[$item] = $value;
        }
        return $this;
    }

    /**
     * 出错自动重试
     * @param int $times
     * @return self
     */
    public function retry($times = 0)
    {
        $this->retry = $times;
        return $this;
    }

    /**
     * 执行Curl操作
     * @param int $retry
     */
    private function execute($retry = 0)
    {
        // 初始化句柄
        $ch = curl_init();

        // 配置选项
        $option = array_merge($this->option, $this->custom);
        foreach($option as $key => $val) {
            if (is_string($key)) {
                $key = constant(strtoupper($key));
            }
            curl_setopt($ch, $key, $val);
        }

        // POST选项
        if ($this->post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post_array($this->post));
        }

        // 运行句柄
        $this->data = (string) curl_exec($ch);
        $this->info = curl_getinfo($ch);

        // 检查错误
        if (curl_errno($ch)) {
            $this->fail = true;
            $this->message = curl_error($ch);
        } else {
            $this->fail = false;
            $this->message = '';
        }

        // 注销句柄
        curl_close($ch);

        // 自动重试
        if ($this->fail && $retry < $this->retry) {
            $this->execute($retry + 1);
        }

        // 注销配置
        $this->post     = array();
        $this->retry    = 0;
        $this->download = false;
    }

    /**
     * 一维化POST信息
     * @param array  $input
     * @param string $pre
     * @return array
     */
    private function post_array($input, $pre = null){
        if (is_array($input)) {
            $output = array();
            foreach ($input as $key => $value) {
                $index = is_null($pre) ? $key : "{$pre}[{$key}]";
                if (is_array($value)) {
                    $output = array_merge($output, $this->post_array($value, $index));
                } else {
                    $output[$index] = $value;
                }
            }
            return $output;
        }
        return $input;
    }
}
