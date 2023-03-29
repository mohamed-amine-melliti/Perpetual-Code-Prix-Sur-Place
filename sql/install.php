<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrixSurPlaceInstall
{
    public function __construct()
    {
        $this->createTable();
    }

    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "product_prix_sur_place` (
            `id_product` INT(11) NOT NULL,
            `prix_sur_place` DECIMAL(20,6) NOT NULL DEFAULT '0.000000',
            PRIMARY KEY (`id_product`)
        ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;";

        return Db::getInstance()->execute($sql);
    }

    public function uninstall()
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "product_prix_sur_place`";
        return Db::getInstance()->execute($sql);
    }
}

$install = new PrixSurPlaceInstall();