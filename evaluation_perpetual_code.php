<?php
/**
 * 2007-2023 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2023 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Evaluation_perpetual_code extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->displayName = $this->l('Prix sur place');
        $this->description = $this->l('Add a field for on-site price on product page');
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'mohamed amine';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Prix sur place');
        $this->description = $this->l('Ajoute un champ supplémentaire pour le prix sur place sur la fiche produit.');

        $this->confirmUninstall = $this->l('sure ?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('EVALUATION_PERPETUAL_CODE_LIVE_MODE', false);

        include(dirname(__FILE__) . '/sql/install.php');

        if (!parent::install() || !$this->registerHook('displayProductPriceBlock')) {
            return false;
        }

        Db::getInstance()->execute('
      ALTER TABLE `' . _DB_PREFIX_ . 'product`
      ADD `prix_sur_place` DECIMAL(20,6) NOT NULL DEFAULT 0
    ');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('displayAttributeForm') &&
            $this->registerHook('displayProductExtraContent')
            && $this->registerHook('actionProductUpdate');

    }

    public function uninstall()
    {
        Configuration::deleteByName('EVALUATION_PERPETUAL_CODE_LIVE_MODE');

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitEvaluation_perpetual_codeModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEvaluation_perpetual_codeModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'EVALUATION_PERPETUAL_CODE_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'EVALUATION_PERPETUAL_CODE_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'EVALUATION_PERPETUAL_CODE_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'EVALUATION_PERPETUAL_CODE_LIVE_MODE' => Configuration::get('EVALUATION_PERPETUAL_CODE_LIVE_MODE', true),
            'EVALUATION_PERPETUAL_CODE_ACCOUNT_EMAIL' => Configuration::get('EVALUATION_PERPETUAL_CODE_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'EVALUATION_PERPETUAL_CODE_ACCOUNT_PASSWORD' => Configuration::get('EVALUATION_PERPETUAL_CODE_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }


    public function hookDisplayAttributeForm()
    {
        /* Place your code here. */
    }

    public function hookDisplayProductExtraContent()
    {
        /* Place your code here. */
    }
    /**************************************************************************/

    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = (int)Tools::getValue('id_product');
        $product = new Product($id_product);

        $this->context->smarty->assign(array(
            'prixsurplace_value' => $product->prix_sur_place,
        ));

        return $this->display(__FILE__, 'views/templates/admin/champs_supp.tpl');
    }
    /**************************************************************************/

    public function hookActionProductUpdate($params)
    {
        $id_product = (int)$params['id_product'];
        $product = new Product($id_product);

        $product->prix_sur_place = (float)Tools::getValue('prix_sur_place');

        if (!$product->update()) {
            return false;
        }

        return true;
    }
/**************************************************************************/
    public function hookDisplayProductAdditionalInfo($params)
    {
        $productId = (int)$params['product']->id;
        $priceOnSite = MyModule::getPriceOnSite($productId);

        if ($priceOnSite) {
            $this->context->smarty->assign(array(
                'price_on_site' => $priceOnSite,
            ));

            return $this->display(__FILE__, 'views/templates/front/product_additional_info.tpl');
        }
    }
/**************************************************************************/
    public static function getPriceOnSite($productId)
    {
        $query = new DbQuery();
        $query->select('price_on_site');
        $query->from('my_module_product');
        $query->where('id_product = '.(int)$productId);

        return Db::getInstance()->getValue($query);
    }
}
