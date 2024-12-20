<?php
if (!defined('_PS_VERSION_')) {
	exit;
}

class OrderCouponStats extends ModuleGrid
{
	private $html;
	private $query;
	private $columns;
	private $default_sort_column;
	private $default_sort_direction;
	private $empty_message;
	private $paging_message;

	public function __construct()
	{
		$this->name = 'ordercouponstats';
		$this->tab = 'analytics_stats';
		$this->version = '1.0.0';
		$this->author = 'Your Name';
		$this->need_instance = 0;

		parent::__construct();

		$this->default_sort_column = 'order_date';
		$this->default_sort_direction = 'DESC';
		$this->empty_message = $this->l('No orders found with coupons.');
		$this->paging_message = sprintf($this->l('Displaying %1$s of %2$s'), '{0} - {1}', '{2}');

		$this->columns = array(
			array(
				'id' => 'id_order',
				'header' => $this->l('Order ID'),
				'dataIndex' => 'id_order',
				'align' => 'center'
			),
			array(
				'id' => 'id_cart_rule',
				'header' => $this->l('Coupon ID'),
				'dataIndex' => 'id_cart_rule',
				'align' => 'center'
			),
			array(
				'id' => 'order_value',
				'header' => $this->l('Order Value (Excl. Tax)'),
				'dataIndex' => 'order_value',
				'align' => 'right'
			),
			array(
				'id' => 'customer_firstname',
				'header' => $this->l('Customer First Name'),
				'dataIndex' => 'customer_firstname',
				'align' => 'left'
			),
			array(
				'id' => 'customer_lastname',
				'header' => $this->l('Customer Last Name'),
				'dataIndex' => 'customer_lastname',
				'align' => 'left'
			),
			array(
				'id' => 'order_date',
				'header' => $this->l('Order Date'),
				'dataIndex' => 'order_date',
				'align' => 'center'
			),
		);

		$this->displayName = $this->l('Order Coupon Statistics');
		$this->description = $this->l('Displays all orders that used a discount coupon.');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
	}

	public function install()
	{
		return parent::install() && $this->registerHook('AdminStatsModules');
	}

	public function hookAdminStatsModules($params)
	{
		$engine_params = array(
			'id' => 'id_order',
			'title' => $this->displayName,
			'columns' => $this->columns,
			'defaultSortColumn' => $this->default_sort_column,
			'defaultSortDirection' => $this->default_sort_direction,
			'emptyMessage' => $this->empty_message,
			'pagingMessage' => $this->paging_message
		);

		if (Tools::getValue('export')) {
			$this->csvExport($engine_params);
		}

		$this->html = '
            <div class="panel-heading">
                ' . $this->displayName . '
            </div>
            ' . $this->engine($engine_params) . '
            <a class="btn btn-default export-csv" href="' . Tools::safeOutput($_SERVER['REQUEST_URI'] . '&export=1') . '">
                <i class="icon-cloud-upload"></i> ' . $this->l('CSV Export') . '
            </a>';

		return $this->html;
	}

	public function getData()
	{
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$this->query = '
            SELECT SQL_CALC_FOUND_ROWS
                o.id_order,
                ocr.id_cart_rule,
                ROUND(o.total_paid_tax_excl, 2) AS order_value,
                c.firstname AS customer_firstname,
                c.lastname AS customer_lastname,
                o.date_add AS order_date
            FROM ' . _DB_PREFIX_ . 'orders o
            INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
            INNER JOIN ' . _DB_PREFIX_ . 'customer c ON o.id_customer = c.id_customer
            WHERE o.valid = 1
                ' . Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o') . '
                AND o.date_add BETWEEN ' . $this->getDate() . '
            ORDER BY ' . pSQL($this->_sort) . ' ' . pSQL($this->_direction) . '
            LIMIT ' . (int)$this->_start . ', ' . (int)$this->_limit;

		$values = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($this->query);
		foreach ($values as &$value) {
			$value['order_value'] = Tools::displayPrice($value['order_value'], $currency);
		}

		$this->_values = $values;
		$this->_totalCount = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');
	}
}
