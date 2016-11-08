<?php
/**
 * Author:  Wenpeng
 * Email:   imwwp@outlook.com
 * Version: 1.0.0
 *
 * https://github.com/wenpeng/curl
 * 一个轻量级的网络操作类，实现GET、POST、UPLOAD、DOWNLOAD常用操作，支持链式写法。
 */

namespace Wenpeng\Curl;

use Exception;

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

    private $info;
    private $data;
    private $error;
    private $message;

    private static $instance;
        
    /**
     * Instance
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
     * Task info
     *
     * @return array
     */
    public function info()
    {
        return $this->info;
    }

    /**
     * Result Data
     *
     * @return string
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Error status
     *
     * @return integer
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * Error message
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * Set POST data
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
     * File upload
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
     * Save file
     * @param string $path
     * @return self
     * @throws Exception
     */
    public function save($path)
    {
        if ($this->error) {
            throw new Exception($this->message, $this->error);
        }
        $fp = @fopen($path, 'w');
        if ($fp === false) {
            throw new Exception('Failed to save the content', 500);
        }
        fwrite($fp, $this->data);
        fclose($fp);
        return $this;
    }

    /**
     * Request URL
     * @param string $url
     * @return self
     * @throws Exception
     */
    public function url($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->set('CURLOPT_URL', $url)->process();
        }
        throw new Exception('Target URL is required.', 500);
    }

    /**
     * Set option
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
     * Set retry times
     * @param int $times
     * @return self
     */
    public function retry($times = 0)
    {
        $this->retry = $times;
        return $this;
    }

    /**
     * Task process
     * @param int $retry
     * @return self
     */
    private function process($retry = 0)
    {
        $ch = curl_init();

        $option = array_merge($this->option, $this->custom);
        foreach($option as $key => $val) {
            if (is_string($key)) {
                $key = constant(strtoupper($key));
            }
            curl_setopt($ch, $key, $val);
        }

        if ($this->post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->convert($this->post));
        }

        $this->data = (string) curl_exec($ch);
        $this->info = curl_getinfo($ch);
        $this->error = curl_errno($ch);
        $this->message = $this->error ? curl_error($ch) : '';

        curl_close($ch);

        if ($this->error && $retry < $this->retry) {
            $this->process($retry + 1);
        }

        $this->post     = array();
        $this->retry    = 0;

        return $this;
    }

    /**
     * Convert array
     * @param array  $input
     * @param string $pre
     * @return array
     */
    private function convert($input, $pre = null){
        if (is_array($input)) {
            $output = array();
            foreach ($input as $key => $value) {
                $index = is_null($pre) ? $key : "{$pre}[{$key}]";
                if (is_array($value)) {
                    $output = array_merge($output, $this->convert($value, $index));
                } else {
                    $output[$index] = $value;
                }
            }
            return $output;
        }
        return $input;
    }
}
