<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd6b62c2a151ad0ae33a0d68a0967e8d6
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Workerman\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Workerman\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/workerman',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd6b62c2a151ad0ae33a0d68a0967e8d6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd6b62c2a151ad0ae33a0d68a0967e8d6::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
