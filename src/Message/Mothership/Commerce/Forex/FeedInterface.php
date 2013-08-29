<?php

namespace Message\Mothership\Commerce\Forex;

interface FeedInterface {

	public function fetch();
	public function getRates($currencies = null);

}