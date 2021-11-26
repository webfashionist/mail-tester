<?php
namespace mailTester;

/**
 * Class Autoloader
 * @package wfCMS
 */
class Autoloader {

    /**
     * Autoloader constructor.
     */
    public function __construct()
    {
        spl_autoload_register([$this, 'loader']);
    }

    public static function start()
    {
        new self();
    }

    /**
     * Include class from the current folder
     * @param string $className Class name
     */
    private function loader(string $className)
    {
        $composerAutoloader = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($composerAutoloader) && is_file($composerAutoloader)) {
            require_once $composerAutoloader;
        }
        if (substr($className, 0, strlen("mailTester")) === "mailTester") {
            $class = explode("\\", $className);
            $className = $class[count($class)-1];
            require_once __DIR__ . '/' . $className . '.class.php';
        }
    }

}
