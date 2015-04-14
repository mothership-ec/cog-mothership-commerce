# Changelog

## 5.2.3

- Exceptions are caught more gracefully when creating a product via the CSV if a value cannot be set against a field on the product
- Added `invalidateRow()` method to `Product\Upload\Validate` for redeclaring a valid row of a CSV as invalid

## 5.2.2

- Fix issue where all products in dashboard are loaded on AJAX requests

## 5.2.1

- Updated CP requirement to 3.2

## 5.2.0

- Added functionality to see which product options are assigned to an image
- Added functionality to delete an image from a product
- Currency cookies are namespaced if set to `ms-commerce-currency` in the config file
- Fixed issue where the Payment screen in the order overview would break if there was a payment against a return

## 5.1.0

- Quantity selector available on product selector
- Deprecated `Message\Mothership\Commerce\Field\ProductUnitInStockOnlyChoiceType`, replaced with `Message\Mothership\Commerce\Form\Extension\Type\UnitChoice`
- Changed data type for IDs on product image table to accept strings, as IDs are MD5 hashes and not integers
- Improved markup for product selector

## 5.0.0

- Initial open source release