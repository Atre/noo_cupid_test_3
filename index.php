<?php

include('adodb5/adodb.inc.php');

class Singleton
{
    protected static $instance = null;

    protected static $driver = 'mysql';

    protected static $connection;

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public function connectByArray($params)
    {
        if (array_key_exists('server', $params) && array_key_exists('user', $params) && array_key_exists('password', $params)
            && array_key_exists('database', $params)
        ) {
            $db = ADONewConnection(self::$driver);
            @$db->Connect($params['server'], $params['user'], $params['password'], $params['database']);
            return $db->_connectionID ? $db : false;
        } else {
            return false;
        }
    }

    public function connectByDSNString($dsnString)
    {
        $db = @ADONewConnection($dsnString);
        return $db;
    }

    public function connectByXML($xml)
    {
        $xml = @simplexml_load_file($xml);
        if ($xml && isset($xml->server) && isset($xml->user) && isset($xml->password) && isset($xml->database)) {
            $db = ADONewConnection(self::$driver);
            @$db->Connect($xml->server, $xml->user, $xml->password, $xml->database);
            return $db->_connectionID ? $db : false;
        } else {
            return false;
        }
    }
}

// Output testing data

// Connect by DSN string
echo 'Connecting by DSN string:<br/>';
$dsnString = 'mysql://roundblo_nooado:dy5ukgqv@roundblo.mysql.ukraine.com.ua/roundblo_nooado';
echo $dsnString;
$db = Singleton::getInstance()->connectByDSNString($dsnString);
$rows = $db->execute('SELECT * FROM mytest')->rowCount();
echo '<br/><br/>Total rows: ' . $rows;

// Connect by xml file on server
echo '<hr>Connect by XML file on server:<br/>';
echo '<pre>';
var_dump(file_get_contents('mysql.xml'));
echo '</pre>';
$db = Singleton::getInstance()->connectByXML('mysql.xml');
$rows = $db->execute('SELECT * FROM mytest')->rowCount();
echo '<br/>Total rows: ' . $rows;

// Connect by array
$connectionSettings = array(
    'server' => 'roundblo.mysql.ukraine.com.ua',
    'user' => 'roundblo_nooado',
    'password' => 'dy5ukgqv',
    'database' => 'roundblo_nooado'
);
echo '<hr>Connect by array of settings:<br/>';
echo '<pre>';
var_dump($connectionSettings);
echo '</pre>';
$db = Singleton::getInstance()->connectByArray($connectionSettings);
$rows = $db->execute('SELECT * FROM mytest')->rowCount();
echo '<br/>Total rows: ' . $rows;

// Test query

if (isset($db)) {
    echo '</br></br>Test query:';
    $db->debug = true;
    $res = $db->Execute('select * from mytest');
    echo '<pre>';
    var_dump($res->GetRows());
    echo '</pre>';
} else {
    throw new Exception('Check your connection settings');
}