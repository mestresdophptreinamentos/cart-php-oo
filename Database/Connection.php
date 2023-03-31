<?php
/*
    ***********************************************
    CONNECTION.PHP - PARAMETRIZAÇÃO DA CONEXÃO COM BANCO DE DADOS DE NOSSA APLICAÇÃO.
    ***********************************************
    Copyright (c) 2022, Jeferson Souza MESTRES DO PHP
*/

namespace Database;

use PDO;
use PDOException;

class Connection
{
    private static $Hostname = 'localhost';
    private static $Username = 'root';
    private static $Password = '';
    private static $Database = 'cartoo';

    /**
     * É um atributo da instância PDO
     * @var PDO
     */
    private static $Conn;

    /**
     * Impedi que a conexão seja realizada fora da classe através do operador: new
     */
    private function __construct() {

    }

    /**
     * Conecta com o banco de dados com o Singleton.
     * Retorna um objeto PDO!
     */
    private static function Pdo()
    {
        if (!isset(self::$Conn)) {
            try {
                $Drivers = 'mysql:host=' . self::$Hostname . ';dbname=' . self::$Database;
                $Complements = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'];

                self::$Conn = new PDO($Drivers, self::$Username, self::$Password, $Complements);

            } catch (PDOException $e) {
                echo "<p class='alert-error color-white text-center'>[Erro]: Não foi possível conectar ao banco de dados...</p>";
                exit;
            }
        }
        return self::$Conn;
    }

    /*
     * Retorna Singleton Pattern.
     */
    public static function getInstance() {
        return self::Pdo();
    }
}