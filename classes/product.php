<?php
class Product extends ProductCore
{
    public $prix_sur_place;

    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
    {
        self::$definition['fields']['prix_sur_place'] = array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'db_type' => 'DECIMAL(17,2)', 'default' => '0.00');

        parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
    }
}