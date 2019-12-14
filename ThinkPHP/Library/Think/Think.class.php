<?php
namespace Think;

class Think
{
    private static $_map      = array(); // ( 类名 => 文件路径) 的映射
    private static $_instance = array(); // 实例化对象

    static public function start()
    {
        spl_autoload_register('Think\Think::autoload');
        register_shutdown_function('Think\Think::fatalError');
        set_error_handler('Think\Think::appError');
        set_exception_handler('Think\Think::appException');
        
        Storage::connect( STORAGE_TYPE );



        // 根据配置，选中一种应用模式： 默认是 common
        $mode = include (is_file(CONF_PATH.'core.php') ? CONF_PATH.'core.php' : MODE_PATH.APP_MODE.'.php');
        
        // 1. 将这个模式下使用到的 所有公有函数、类 等，全部 include 进来，不走 autoload
        foreach ($mode['core'] as $file)
        {
            include $file;
        }

        // 2. 加载 该应用模式 下的所有 配置
        foreach ($mode['config'] as $key => $file)
        {
            is_numeric($key) ? C(load_config($file)) : C($key,load_config($file));
        }

        // 3. 再加载，用户使用 config_APP_MODE.php 方式自定义的 配置
        if('common' != APP_MODE && is_file(CONF_PATH.'config_'.APP_MODE.CONF_EXT))
            C(load_config(CONF_PATH.'config_'.APP_MODE.CONF_EXT));  

        // 4. 将配置中定义的 ( 类名 => 类路径 ), 全部加载到，$_map 中，加快类的 autoload 速度
        if(isset($mode['alias'])) self::addMap( is_array($mode['alias']) ? $mode['alias'] : include $mode['alias'] );
        if(is_file(CONF_PATH.'alias.php')) self::addMap(include CONF_PATH.'alias.php');

        // 5. 加载 行为定义
        if(isset($mode['tags'])) Hook::import(is_array($mode['tags'])?$mode['tags']:include $mode['tags']);
        if(is_file(CONF_PATH.'tags.php')) Hook::import(include CONF_PATH.'tags.php');

        // 6. 再加载各种 杂七杂八 的定义
        C(include THINK_PATH.'Conf/debug.php');
        if(is_file(CONF_PATH.'debug'.CONF_EXT)) C(include CONF_PATH.'debug'.CONF_EXT);
        if(APP_STATUS && is_file(CONF_PATH.APP_STATUS.CONF_EXT)) C(include CONF_PATH.APP_STATUS.CONF_EXT);   

        // 7. 设置下时区，加载下语言包 等杂事
        L(include THINK_PATH.'Lang/'.strtolower(C('DEFAULT_LANG')).'.php');
        date_default_timezone_set( C('DEFAULT_TIMEZONE') );
        G('loadTime');

        // 8. 启动应用 Think\App::run
        App::run();
    }

    static public function addMap($class, $map=''){
        if(is_array($class))
            self::$_map = array_merge(self::$_map, $class);
        else
            self::$_map[$class] = $map;
    }

    static public function getMap($class=''){
        if( '' === $class) {
            return self::$_map;
        }elseif(isset(self::$_map[$class])){
            return self::$_map[$class];
        }else{
            return null;
        }
    }

    public static function autoload($class)
    {
        // 使用映射，最块！
        if(isset(self::$_map[$class]))
        {
            include self::$_map[$class];
        }
        elseif( false !== strpos($class,'\\'))
        {
          $name           =   strstr($class, '\\', true);
          if(in_array($name,array('Think','Org','Behavior','Com','Vendor')) || is_dir(LIB_PATH.$name)){ 
              // Library目录下面的命名空间自动定位
              $path       =   LIB_PATH;
          }else{
              // 检测自定义命名空间 否则就以模块为命名空间
              $namespace  =   C('AUTOLOAD_NAMESPACE');
              $path       =   isset($namespace[$name])? dirname($namespace[$name]).'/' : APP_PATH;
          }
          $filename       =   $path . str_replace('\\', '/', $class) . EXT;
          if(is_file($filename)) {
              // Win环境下面严格区分大小写
              if (IS_WIN && false === strpos(str_replace('/', '\\', realpath($filename)), $class . EXT)){
                  return ;
              }
              include $filename;
          }
        }
        elseif (!C('APP_USE_NAMESPACE'))
        {
            // 自动加载的类库层
            foreach(explode(',',C('APP_AUTOLOAD_LAYER')) as $layer){
                if(substr($class,-strlen($layer))==$layer){
                    if(require_cache(MODULE_PATH.$layer.'/'.$class.EXT)) {
                        return ;
                    }
                }            
            }
            // 根据自动加载路径设置进行尝试搜索
            foreach (explode(',',C('APP_AUTOLOAD_PATH')) as $path){
                if(import($path.'.'.$class))
                    return ;
            }
        }
    }

    /**
     * 取得对象实例 支持调用类的静态方法
     * @param string $class 对象类名
     * @param string $method 类的静态方法名
     * @return object
     */
    static public function instance($class,$method='') {
        $identify   =   $class.$method;
        if(!isset(self::$_instance[$identify])) {
            if(class_exists($class)){
                $o = new $class();
                if(!empty($method) && method_exists($o,$method))
                    self::$_instance[$identify] = call_user_func(array(&$o, $method));
                else
                    self::$_instance[$identify] = $o;
            }
            else
                self::halt(L('_CLASS_NOT_EXIST_').':'.$class);
        }
        return self::$_instance[$identify];
    }

    /**
     * 自定义异常处理
     * @access public
     * @param mixed $e 异常对象
     */
    static public function appException($e) {
        $error = array();
        $error['message']   =   $e->getMessage();
        $trace              =   $e->getTrace();
        if('E'==$trace[0]['function']) {
            $error['file']  =   $trace[0]['file'];
            $error['line']  =   $trace[0]['line'];
        }else{
            $error['file']  =   $e->getFile();
            $error['line']  =   $e->getLine();
        }
        $error['trace']     =   $e->getTraceAsString();
        Log::record($error['message'],Log::ERR);
        // 发送404信息
        header('HTTP/1.1 404 Not Found');
        header('Status:404 Not Found');
        self::halt($error);
    }

    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    static public function appError($errno, $errstr, $errfile, $errline) {
      switch ($errno) {
          case E_ERROR:
          case E_PARSE:
          case E_CORE_ERROR:
          case E_COMPILE_ERROR:
          case E_USER_ERROR:
            ob_end_clean();
            $errorStr = "$errstr ".$errfile." 第 $errline 行.";
            if(C('LOG_RECORD')) Log::write("[$errno] ".$errorStr,Log::ERR);
            self::halt($errorStr);
            break;
          default:
            $errorStr = "[$errno] $errstr ".$errfile." 第 $errline 行.";
            self::trace($errorStr,'','NOTIC');
            break;
      }
    }
    
    // 致命错误捕获
    static public function fatalError() {
        Log::save();
        if ($e = error_get_last()) {
            switch($e['type']){
              case E_ERROR:
              case E_PARSE:
              case E_CORE_ERROR:
              case E_COMPILE_ERROR:
              case E_USER_ERROR:  
                ob_end_clean();
                self::halt($e);
                break;
            }
        }
    }

    /**
     * 错误输出
     * @param mixed $error 错误
     * @return void
     */
    static public function halt($error) {
        $e = array();
        if (APP_DEBUG || IS_CLI) {
            //调试模式下输出错误信息
            if (!is_array($error)) {
                $trace          = debug_backtrace();
                $e['message']   = $error;
                $e['file']      = $trace[0]['file'];
                $e['line']      = $trace[0]['line'];
                ob_start();
                debug_print_backtrace();
                $e['trace']     = ob_get_clean();
            } else {
                $e              = $error;
            }
            if(IS_CLI){
                exit(iconv('UTF-8','gbk',$e['message']).PHP_EOL.'FILE: '.$e['file'].'('.$e['line'].')'.PHP_EOL.$e['trace']);
            }
        } else {
            //否则定向到错误页面
            $error_page         = C('ERROR_PAGE');
            if (!empty($error_page)) {
                redirect($error_page);
            } else {
                $message        = is_array($error) ? $error['message'] : $error;
                $e['message']   = C('SHOW_ERROR_MSG')? $message : C('ERROR_MESSAGE');
            }
        }
        // 包含异常页面模板
        $exceptionFile =  C('TMPL_EXCEPTION_FILE',null,THINK_PATH.'Tpl/think_exception.tpl');
        include $exceptionFile;
        exit;
    }

    /**
     * 添加和获取页面Trace记录
     * @param string $value 变量
     * @param string $label 标签
     * @param string $level 日志级别(或者页面Trace的选项卡)
     * @param boolean $record 是否记录日志
     * @return void|array
     */
    static public function trace($value='[think]',$label='',$level='DEBUG',$record=false) {
        static $_trace =  array();
        if('[think]' === $value){ // 获取trace信息
            return $_trace;
        }else{
            $info   =   ($label?$label.':':'').print_r($value,true);
            $level  =   strtoupper($level);
            
            if((defined('IS_AJAX') && IS_AJAX) || !C('SHOW_PAGE_TRACE')  || $record) {
                Log::record($info,$level,$record);
            }else{
                if(!isset($_trace[$level]) || count($_trace[$level])>C('TRACE_MAX_RECORD')) {
                    $_trace[$level] =   array();
                }
                $_trace[$level][]   =   $info;
            }
        }
    }

}
