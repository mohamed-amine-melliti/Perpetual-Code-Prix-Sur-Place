<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrixSurPlaceUninstall
{
    public function __construct()
    {
        $this->dropTable();
    }

    public function dropTable()
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "product_prix_sur_place`";
        return Db::getInstance()->execute($sql);
    }
}

$uninstall = new PrixSurPlaceUninstall();
