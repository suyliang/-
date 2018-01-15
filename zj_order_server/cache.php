<?php  
  
class Cache {  
  
    private $dir = 'cache';  
    private $expiration = 14400;  
  
    function __construct($dir = '')  
    {  
        if(!empty($dir)){ $this->dir = $dir; }  
    }  
  
    private function _name($key)  
    {  
        return sprintf("%s/%s", $this->dir, sha1($key));  
    }  
  
    public function get($key, $expiration = '')  
    {  
  
        if ( !is_dir($this->dir) OR !is_writable($this->dir))  
        {  
            return FALSE;  
        }  
  
        $cache_path = $this->_name($key);  
  
        if (!@file_exists($cache_path))  
        {  
            return FALSE;  
        }  
  
        $expiration = empty($expiration) ? $this->expiration : $expiration;  
  
        if (filemtime($cache_path) < (time() - $expiration))  
        {  
            $this->clear($key);  
            return FALSE;  
        }  
  
        if (!$fp = @fopen($cache_path, 'rb'))  
        {  
            return FALSE;  
        }  
  
        flock($fp, LOCK_SH);  
  
        $cache = '';  
  
        if (filesize($cache_path) > 0)  
        {  
            $cache = unserialize(fread($fp, filesize($cache_path)));  
        }  
        else  
        {  
            $cache = NULL;  
        }  
  
        flock($fp, LOCK_UN);  
        fclose($fp);  
  
        return $cache;  
    }  
  
    public function set($key, $data)  
    {  
  
        if ( !is_dir($this->dir) OR !is_writable($this->dir))  
        {  
             $this->_makeDir($this->dir);  
        }  
  
        $cache_path = $this->_name($key);  
  
        if ( ! $fp = fopen($cache_path, 'wb'))  
        {  
            return FALSE;  
        }  
  
        if (flock($fp, LOCK_EX))  
        {  
            fwrite($fp, serialize($data));  
            flock($fp, LOCK_UN);  
        }  
        else  
        {  
            return FALSE;  
        }  
        fclose($fp);  
        @chmod($cache_path, 0777);  
        return TRUE;  
    }  
  
    public function clear($key)  
    {  
        $cache_path = $this->_name($key);  
  
        if (file_exists($cache_path))  
        {  
            unlink($cache_path);  
            return TRUE;  
        }  
  
        return FALSE;  
    }  
  
    public function clearAll()  
    {  
        $dir = $this->dir;  
        if (is_dir($dir))   
        {  
            $dh=opendir($dir);  
  
            while (false !== ( $file = readdir ($dh)))   
            {  
  
                if($file!="." && $file!="..")   
                {   
                    $fullpath=$dir."/".$file;  
                    if(!is_dir($fullpath)) {  
                        unlink($fullpath);  
                    } else {  
                        delfile($fullpath);  
                    }  
                }  
            }  
            closedir($dh);  
            // rmdir($dir);  
        }  
  }  
  
   private function _makeDir( $dir, $mode = "0777" ) {  
        if( ! $dir ) return 0;  
        $dir = str_replace( "\\", "/", $dir );  
      
        $mdir = "";  
        foreach( explode( "/", $dir ) as $val ) {  
          $mdir .= $val."/";  
          if( $val == ".." || $val == "." || trim( $val ) == "" ) continue;  
            
          if( ! file_exists( $mdir ) ) {  
            if(!@mkdir( $mdir, $mode )){  
             return false;  
            }  
          }  
        }  
        return true;  
    }  
}  