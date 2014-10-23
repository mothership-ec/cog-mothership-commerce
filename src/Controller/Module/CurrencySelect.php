<?php

namespace Message\Mothership\Commerce\Controller\Module;

use Message\Cog\Controller\Controller;
use Message\Cog\HTTP\Cookie;

class CurrencySelect extends Controller
{
	public function form()
	{
		$form = $this->createForm($this->get('currency.form.select'), null, [
			'action' => $this->generateUrl('ms.commerce.currency'),
			'data'   => ['currency' => $this->get('currency')],
		]);

		return $this->render('Message:Mothership:Commerce::currency_form', ['form' => $form]);
	}

	public function process()
	{
		$form = $this->createForm($this->get('currency.form.select'));
		$form->handleRequest();

		if ($form->isValid()) {
			$this->get('http.cookies')
				->add(new Cookie(
						$this->get('cfg')->currency->cookieName, 
						$form->getData()['currency'], 
						date(time() + 9999999))); // dont expire anytime soon
		}

		return $this->redirectToReferer();
	}
}