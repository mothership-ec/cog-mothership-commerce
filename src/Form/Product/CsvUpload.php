<?php

namespace Message\Mothership\Commerce\Form\Product;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;

use Message\Mothership\Commerce\Constraint\Product\Csv as CsvConstraint;
use Message\Mothership\Commerce\Product\Type\FieldCrawler;

class CsvUpload extends Form\AbstractType
{
	/**
	 * @var \Message\Mothership\Commerce\Product\Type\FieldCrawler
	 */
	private $_fieldCrawler;

	public function __construct(FieldCrawler $fieldCrawler)
	{
		$this->_fieldCrawler = $fieldCrawler;
	}

	public function getName()
	{
		return 'ms_csv_upload';
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder->add('file', 'file', [
			'label' => 'ms.commerce.product.upload.form.upload_field',
			'constraints' => [
				new Constraints\NotBlank,
				new CsvConstraint,
			]
		]);
	}
}