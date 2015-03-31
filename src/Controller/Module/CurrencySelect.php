<?php

namespace Message\Mothership\Commerce\Controller\Module;

use Message\Cog\Controller\Controller;
use Message\Cog\HTTP\Cookie;
use Message\Mothership\Commerce\Events;
use Message\Mothership\Commerce\Event\CurrencyChangeEvent;

class CurrencySelect extends Controller
{
	public function form()
	{
		$form = $this->createForm($this->get('currency.form.select'), null, [
			'action' => $this->generateUrl('ms.commerce.currency'),
			'data'   => ['currency' => $this->get('currency')],
			'expanded' => true,
		]);

		return $this->render('Message:Mothership:Commerce::currency_form', [
			'form' => $form, 'currency' => $this->get('currency'),
		]);
	}

	public function process()
	{
		$form = $this->createForm($this->get('currency.form.select'));
		$form->handleRequest();

		if ($form->isValid()) {
			$currency = $form->getData()['currency'];

			$this->get('event.dispatcher')->dispatch(
				Events::CURRENCY_CHANGE,
				new CurrencyChangeEvent($currency)
			);

			$this->get('http.cookies')
				->add(new Cookie(
						$this->get('currency.cookie.name'), 
						$currency, 
						date(time() + 9999999))); // dont expire anytime soon
		}

		return $this->redirectToReferer();
	}
}