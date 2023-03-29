<?php
class ProductController extends ProductControllerCore
{
    public function initContent()
    {
        parent::initContent();

        $id_product = (int) Tools::getValue('id_product');
        $product = new Product($id_product);

        $this->context->smarty->assign(array(
            'prixsurplace_value' => $product->prix_sur_place,
        ));
    }
}
