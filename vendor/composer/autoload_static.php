<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit149ec80d33a6cdc1a2bc4465ff6708bc
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'CoinGate\\' => 9,
            'CoinGatePayment\\Shopware6\\' => 26,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'CoinGate\\' => 
        array (
            0 => __DIR__ . '/..' . '/coingate/coingate-php/lib',
            1 => __DIR__ . '/..' . '/coingate/coingate-php/lib',
        ),
        'CoinGatePayment\\Shopware6\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit149ec80d33a6cdc1a2bc4465ff6708bc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit149ec80d33a6cdc1a2bc4465ff6708bc::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit149ec80d33a6cdc1a2bc4465ff6708bc::$classMap;

        }, null, ClassLoader::class);
    }
}
