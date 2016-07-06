<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Get the configuration or a part of it by its key.
 * 
 * 
 * @staticvar type $config
 * @param type $key
 *     Optionele configuratie key. Indien geen, geef volledige config.
 * @param mixed $default
 *     Optionele waarde indien de config variable niet bestaat.
 * @return type
 */
function app_config($key  = null, $default = NULL) {
    static $config;
    
    if (empty($config)) {
        require_once DIR_APP . '/config.php';
    }
    
    if (!is_null($key)) {
        if (isset($config[$key])) {
            return $config[$key];
        }
        return $default;
    }
    
    return $config;
}

/**
 * Connectie met de database.
 * 
 * @staticvar type $link
 * 
 * @return mysqli
 */
function app_db() {
    static $link;
    
    if (is_null($link)) {
        $config = app_config('DBconn');

        $link = mysqli_connect(
            $config['host'], 
            $config['user'], 
            $config['pass'], 
            $config['name']
        );

        if (!$link) {
            trigger_error('Fout bij verbinden met database: ' . mysqli_connect_error());
        }

        $link->set_charset('utf8');
    }
    
    return $link;
}
