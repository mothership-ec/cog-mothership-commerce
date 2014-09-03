<?php

namespace Message\Mothership\Commerce\Form\Product;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;

use Message\Mothership\Commerce\Constraint\Product\Csv as CsvConstraint;
use Message\Mothership\Commerce\Product\Type\FieldCrawler;

class CsvUpload extends Form\AbstractType
{
	public function getName()
	{
		return 'ms_csv_upload';
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder->add(
			$builder->create(
				'file', 'file', [
				'label' => 'ms.commerce.product.upload.form.upload_field',
				'constraints' => [
					new Constraints\NotBlank,
					new CsvConstraint,
				]
			])
			->addModelTransformer(new DataTransform\ArrayToCsvTransformer)
		);
	}
}