<?php return array(
    'root' => array(
        'pretty_version' => '1.0.0',
        'version' => '1.0.0.0',
        'type' => 'shopware-platform-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => NULL,
        'name' => 'coingate/shopware6-plugin',
        'dev' => false,
    ),
    'versions' => array(
        'coingate/coingate-php' => array(
            'pretty_version' => 'v4.1.0',
            'version' => '4.1.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../coingate/coingate-php',
            'aliases' => array(),
            'reference' => 'e6758806364f00a75097ac7a0aee97c03d462085',
            'dev_requirement' => false,
        ),
        'coingate/shopware6-plugin' => array(
            'pretty_version' => '1.0.0',
            'version' => '1.0.0.0',
            'type' => 'shopware-platform-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => NULL,
            'dev_requirement' => false,
        ),
    ),
);
