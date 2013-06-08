<?php
$loggingConfiguration =
    array(
        'rootLogger' => array(
            'appenders' => array('default'),
        ),
        'appenders' => array(
            'default' => array(
                'class' => 'LoggerAppenderRollingFile',
                'layout' => array(
                    'class' => 'LoggerLayoutSimple'
                ),
                'params' => array(
                    'file' => '/var/www/cicada/log/cicada.log',
                    'maxFileSize' => '1MB',
                    'maxBackupIndex' => '10',
                    'append' => true
                )
            )
        )
    );