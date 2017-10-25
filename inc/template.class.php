<?php

namespace AventurienSolo {

class Template {
    protected $file;
    protected $values = array();
  
    public function __construct($file) {
        $this->file = $file;
    }

    public function set($key, $value) {
        $this->values[$key] = $value;
    }

    public function setObject($object) {
        $array = (array)$object;
        foreach ($array as $key => $value) {
            $key = ucwords($key, "_");
            $key = str_replace("_", "", $key);
            $this->set($key, $value);
        }
    }
  
    public function output() {
        if (!file_exists($this->file)) {
            return "Error loading template file ($this->file).";
        }
        $output = file_get_contents($this->file);
  
        foreach ($this->values as $key => $value) {
            $tagToReplace = "[@$key]";
            $output = str_replace($tagToReplace, $value, $output);
        }
  
        return $output;
    }
}

function get_all_files($path) {
    $all_files = array();
    
    $dirs = get_subdirs($path);
    foreach ($dirs as $dir) {
        $subdirs = get_subdirs($path . DIRECTORY_SEPARATOR . $dir);
        foreach ($subdirs as $subdir) {
            $files = get_files($path . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $subdir);
            foreach ($files as $file) {
                $file = $dir . "/" . $subdir . "/" . $file;
                $file = mb_convert_encoding($file, 'UTF-8', 'Windows-1252');
                array_push($all_files, $file);
            }
        }
    }
    sort($all_files);
    return $all_files;
}

function get_files($path) {
    $files = array();
    if ($handle = opendir($path)) {
        while (false !== ($file = readdir($handle))) {
            if (!is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                array_push($files, $file);
            }
        }
        closedir($handle);
    }
    return $files;
}

function get_subdirs($path) {
    $subdirs = array();
    if ($handle = opendir($path)) {
        $blacklist = array('.', '..');
        while (false !== ($file = readdir($handle))) {
            if (!in_array($file, $blacklist) && is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                array_push($subdirs, $file);
            }
        }
        closedir($handle);
    }
    return $subdirs;
}

}

?>