<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement;

use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Stock\Location\Location;

/**
 * This class represents partial movements.
 * This means, it does not include all adjustments but
 * adjustments filtered after a certain criteria.
 */
class PartialMovement extends Movement
{

}