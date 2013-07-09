<?php

class OrderItemCollection extends OrderCollection
{
	
	protected $required = array(
		'description',
		'price',
	   	'taxCode',
	   	'unitID',
	   	'productID',
	   	'productName'
	);
	
	protected $bundles;
	
	//REGISTER A BUNDLE SO THAT ITEMS CAN BE MAPPED TO THE RETURNED BUNDLE KEY
	public function registerBundle(OrderBundle $bundle)
	{
		$this->bundles[] = $bundle;
	}
	
	//ADD ITEM TO COLLECTION AND RETURN INDEX
	public function add(OrderItem $item)
	{
		//WHEN CREATING AN ORDER, MAP BUNDLE ITEMS TO THEIR BUNDLES
		if (!$item->itemID && !is_null($item->bundleKey)) {
			$item->bundleKey(NULL);
			foreach ($this->bundles as $bundle) {
				$key = $bundle->getBundleKey($item);
				if (is_int($key)) {
					$item->bundleKey($key);
					break;
				}
			}
			//IF WE FAILED TO LINK THIS ITEM TO A BUNDLE, FLAG IT
			if ($key === false) {
				//throw new OrderException('Unable to link ' . $item->description . ' to a bundle');
			}
		}
		$this->items[] = $item;
		return count($this->items) - 1;
	}
	
	//RESET COLLECTION AND LOAD ORDER ITEMS INTO COLLECTION
	public function load()
	{
		if ($this->orderID && !$this->items) {
			$DB = new DBquery;
			$query = 'SELECT '
				   . 'order_id         AS orderID, '
				   . 'item_id          AS itemID, '
				   . 'bundle_key       AS bundleKey, '
				   . 'item_description AS description, '
				   . 'item_description_localised AS descriptionLocalised, '
				   . 'item_original_price AS originalPrice, '
				   . 'item_price       AS price, '
				   . 'item_rrp         AS rrp, '
				   . 'item_discount    AS discount, '
				   . 'order_taxable    AS taxable, '
				   . 'item_tax         AS tax, '
				   . 'item_tax_code    AS taxCode, '
				   . 'item_tax_rate    AS taxRate, '
				   . 'item_weight      AS weight, '
				   . 'item_note        AS note, '
				   . 'unit_id          AS unitID, '
				   . 'unit_name        AS unitName, '
				   . 'unit_cost        AS unitCost, '
				   . 'barcode          AS barcode, '
				   . 'product_id       AS productID, '
				   . 'product_name     AS productName, '
				   . 'style_id         AS styleID, '
				   . 'style_name       AS styleName, '
				   . 'size_id          AS sizeID, '
				   . 'size_name        AS sizeName, '
				   . 'brand_id         AS brandID, '
				   . 'brand_name       AS brandName, '
				   . 'staff_id         AS staffID, '
				   . 'status_id        AS statusID, '
				   . 'status_name      AS statusName, '
				   . 'catalogue_id     AS catalogueID, '
				   . 'picking_description AS pickingDescription, '
				   . 'supplier_ref     AS supplierRef, '
				   . 'cross_sold_from  AS crossSoldFrom, '
				   . 'IF(status_datetime IS NOT NULL, UNIX_TIMESTAMP(status_datetime), UNIX_TIMESTAMP(order_datetime)) AS statusDate, '
				   . 'sender_name      AS senderName, '
				   . 'recipient_name   AS recipientName, '
				   . 'recipient_email  AS recipientEmail, '
				   . 'message          AS recipientMessage '

				   . 'FROM ('
				   . 'SELECT order_item.*, order_summary.order_taxable, '
				   . 'IF( MAX(order_item_status.status_id) IS NOT NULL, MAX(order_item_status.status_id), 0) AS status_id, '
				   . 'MAX(status_datetime) AS status_datetime, order_datetime, staff_id, sender_name, recipient_name, recipient_email, message '
				   . 'FROM order_summary '
				   . 'JOIN order_item USING (order_id) '
				   . 'LEFT JOIN order_item_personalisation USING (item_id) '
				   . 'LEFT JOIN ( '

				   . 'SELECT s1.* '
				   . 'FROM order_item_status AS s1 '
				   . 'LEFT JOIN order_item_status AS s2 '
				   . 'ON s1.item_id = s2.item_id  '
				   . 'AND s1.status_id < s2.status_id '
				   . 'WHERE s1.order_id = ' . $this->orderID . ' '
				   . 'AND s2.status_id IS NULL '
				
				   . ') AS order_item_status USING (item_id) '
				   . 'WHERE order_summary.order_id = ' . $this->orderID . ' '
				   . 'GROUP BY order_item.item_id '
				   . 'ORDER BY order_item.item_id'
				   . ') AS order_items '
				   
				   . 'JOIN ('
				   . 'SELECT status_id, status_name '
				   . 'FROM order_status_name '
				   . 'ORDER BY status_id'
				   . ') AS status_table USING (status_id) '
				   . 'ORDER BY item_id ASC';
				
			if ($DB->query($query)) {
				while ($data = $DB->row()) {
					switch($data['productID']) {
						case Config::get('gifting')->voucher->paper->productID:
							$item = new OrderItemGiftVoucher;
							break;
						case Config::get('gifting')->voucher->electronic->productID:
							$item = new OrderItemGiftVoucherElectronic;
							break;
						default:
							$item = new OrderItem;
					}
					$item->addData($data);
					$this->add($item);
				}
			} else {
				//dump($DB->error());
				throw new OrderException('Unable to load order items');
			}
		}
	}

	//PASS INSERT QUERY BACK TO THE CALLING OBJECT
	public function getInsertQuery($orderID)
	{
		$DB = new DBquery;
		$inserts = array();
		foreach ($this->items as $item) {
			if (is_null($item->itemID)) {
				$this->validate($item);
				
				if($item instanceof OrderItemGiftVoucher) {
					$item->commit();
				}

				$values = array(
					$orderID,
					$DB->escape($item->description),
					$DB->escape($item->descriptionLocalised), 
					$DB->null(  $item->originalPrice),
					$DB->null(  $item->price),
					$DB->null(  $item->rrp),
					$DB->null(  $item->discount),
					$DB->null(  $item->tax),
					$DB->escape($item->taxCode),
					$DB->null(  $item->taxRate),
					$DB->null(  $item->weight),
					$DB->escape($item->note),
					$DB->null(  $item->unitID),
					$DB->escape($item->unitName),
					$DB->null(  $item->unitCost),
					$DB->escape($item->barcode),
					$DB->null(  $item->productID),
					$DB->escape($item->productName),
					$DB->null(  $item->styleID),
					$DB->escape($item->styleName),
					$DB->null(  $item->sizeID),
					$DB->escape($item->sizeName),
					$DB->null(  $item->brandID),
					$DB->escape($item->brandName),
					$DB->null(  $item->catalogueID),
					$DB->escape($item->pickingDescription),
					$DB->escape($item->supplierRef),
					$DB->null(  $item->crossSoldFrom),
					$DB->null(  $item->bundleKey),
				);
				
				$inserts[] = '
					INSERT INTO order_item (
						order_id, 
						item_description,
						item_description_localised,
						item_original_price,
						item_price,
						item_rrp,
						item_discount,
						item_tax,
						item_tax_code,
						item_tax_rate,
						item_weight,
						item_note,
						unit_id,
						unit_name,
						unit_cost,
						barcode,
						product_id,
						product_name,
						style_id,
						style_name,
						size_id,
						size_name,
						brand_id,
						brand_name,
						catalogue_id,
						picking_description,
						supplier_ref,
						cross_sold_from,
						bundle_key
					) VALUES (' . implode(', ', $values) . ')';

				if ($item->isPersonalised()) {
					$inserts[] = 'SET @ITEM_ID = LAST_INSERT_ID()';
					$inserts[] = '
						INSERT INTO
							order_item_personalisation
						SET
							item_id         = @ITEM_ID,
							sender_name     = ' . $DB->escape($item->senderName) . ',
							recipient_name  = ' . $DB->escape($item->recipientName) . ',
							recipient_email = ' . $DB->escape($item->recipientEmail) . ',
							message         = ' . $DB->escape($item->recipientMessage) . '
					';
				}
			}
		}

		return $inserts;
	}

}