<?php

namespace Message\Mothership\Commerce\Form\Product;

use Message\Cog\Routing\UrlGenerator;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CsvUploadConfirm extends Form\AbstractType
{
	private $_urlGenerator;

	public function __construct(UrlGenerator $urlGenerator)
	{
		$this->_urlGenerator = $urlGenerator;
	}

	public function getName()
	{
		return 'ms_csv_upload_confirm';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults([
			'action' => $this->_urlGenerator->generate('ms.commerce.product.upload.create'),
		]);
	}
}