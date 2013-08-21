# Product\Stock
`Product\Stock` is responsible for handling stock changes:

## General
Every change in stock level is documented as a stock adjustment(`Movement\Adjustment\Adjustment`), consisting of the difference in stock level(delta), a unit and a stock location (see 'Locations').

The container which holds adjustment is the stock movement(`Movement\Movement`). It also stores general information about the stock movement, like:

* reason: Can be any reason defined in the `ReasonCollection` (see 'Reason Collection')
* authorship: Date and Author of the movement
* automated: Whether the movement has automatically been created

Both movements and adjustments have a create-decorator and a class to load them, altough only the `Movement\Loader` has its own a service: `stock.movement.loader`.

The loader has methods to get movements by product, unit, movement-id and by location.
From inside a controller, you could for example get all movements for a certain unit like this:

	$brownJacket = … // get unit
	$movementLoader = $this->get('stock.movement.loader);
	$movements = $movementLoader->getByUnit($brownJacket);

## Movements and Partial Movements
When loading movements using `Movement\Loader`, Movements can either have all adjustments(for example when you load a movement by ID) or just a selection of adjustments which match a certain criteria (for example when loading all movements and adjustments for a certain unit).

The movements which have all their adjustments set, are instances of `Movement\Movement`, whereas the other ones are `Movement\PartialMovement`s, which allows to differenciate the two (e.g. when debugging).

PartialMovement extends Movement and does not add any additional functionality, what means there is no difference between the two classes other than the name.


## The Stock Manager
`StockManager` is a service providing an interface to adjust stock levels. The service is called 'stock.manager'.

	$stockManager = $this->get('stock.manager'); // get service
	
It is responsible for both - saving stock movements and updating the actual stock level in the database on an transactional basis.
The stock manager internally has a `Movement\Movement` which is filled with adjustments by using the following methods:

* `increment`: increments the stock level for given unit and location (by the provided value)
* `decrement`: decrements the stock level for given unit and location (by the provided value)
* `set`: sets the stock level for given unit and location to a specified value

Also, the stock manager has methods to set the movement's properties:

* `setReason`: Every movement **must** have a reason, see `Movement\Reason` for details.
* `setNote`: Optional string (handy for storing e.g. order-ids on the movement)
* `setAutomated`: Whether the movement was generated automatically or not

Once all the adjustments are added to the stock manager, the changes can be changed by calling `commit()` which will then save all changes to a transaction and commit it.


This could be used like:

	$stockManager = $this->get('stock.manager'); // get service
	
	$brownJacket = … 	// get first unit
	$whiteJacket = … 	// get second unit
	
	$web = $locationCollection->get('web');
	$reason = $reasonCollection->get('new_order');

	$stockManager->increment(
		$brownJacket,
		$web,				
		5
	);
	
	$stockManager->set(
		$whiteJacket,
		$web,				
		0
	);
	
	$stockManager->setReason($reason);
	$stockManager->setAutomated(false);
	
	if($stockManager->commit()) {
		echo 'Success!';
	}
	
	

## Stock Change Event
**Doesn't exist yet, sorry!**

## Movement\Iterator
The iterator is a class which iterates over stock movements for a set of given units. It is accessible through the service `stock.movement.iterator`.
This allows us to go through the stock history for these units and get stock levels before and after every movement.
To set the units, there are two methods:

* `addUnits()`, which adds an array of units by calling
* `addUnit()`, which adds one unit and loads all movements of that unit

As the `Movement\Iterator` implements `\Iterator`, it can be iterated through with foreach-loops.
The most important methods for displaying the stock changes when iterating over them are:

* `hasStock(unit, location)`: tells you whether there was an adjustment in stock for a specified unit and location in the current movement
* `getStockBefore(unit, location)`: returns the stock before the adjustments for the specified unit and location in the current movement
* `getStockAfter(unit, location)`: returns the stock after the adjustments for the specified unit and location in the current movement
* `getLastStock(unit, location)`: returns the last stock saved in the iterators internal counter or - if there has not been an adjustment for that unit and location yet - returns the current stock level

Moreover there is the `getStockForMovement(movement, unit, location)` method, which returns the stock level at the time of the given movement.

Inside a controller you could use the movement iterator to render the history:

	$movementIterator = $this->get('stock.movement.iterator);
	$movementIterator
		->addUnits($arrayOfUnits)
		->addUnit($anotherUnit);
		
	$this->render('template', array(
		'iterator' => $movementIterator
	));
		
Your template then could look something like:

	{% for movement in movementIterator %} // iterate over all movements
		<h1>Movement #{{ movement.id }}</h1>
		<p>
			<strong>Reason:</strong> {{ movement.reason }}
		</p>
		<p>
			<strong>Note:</strong> {{ movement.note }}
		</p>
		
		{% for adjustment in movement.adjustments %}
			<section>
				<h1>
					{{ adjustment.unit.barcode }}  // 
					change in {{ adjustment.location }}
				</h1>
				<ul>
					<li>
						{{ iterator.getStockBefore }} // stock before
					</li>
					<li>
						{{ '%+d'|format(adjustment.delta) }} // add plus-sign
					</li>
					<li>
						{{ iterator.getStockAfter }} // stock after
					</li>
				</ul>
			</section>
		{% endfor %}
	{% endfor %}

## Location
A stock location basically only has a short name(e.g. 'web') to identify it by and a display name(e.g. 'Web Stock').

More important than the actual location entity is the `Location\Collection`, which can be accessed as service(`stock.locations`).
New locations can be added in the general configurations for the specific client(Client/Commerce/Bootstrap/Services).

The location name should preferably be short and has to be unique, as they are used as keys in the collection and as identifiers in the database.
To load a location by the name just call `$locationCollection->get('name');` for getting an array of all locations, use `$locationCollection->all();`.

## Movement\Reason
As with the location, `Movement\Reason` also consists of two classes: The actual entity `Movement\Reason\Reason` and the Collection `Movement\Reason\Collection`.

The reason entity also only consists of a unique name(e.g. 'new_order') and a description(e.g. 'New Order').
The Collection offers methods for getting all set reasons:

	$reasons = $reasonCollection->all();

And there is a method for getting them by name:

	$newOrderReason = reasonCollection->get('new_order');
	
To add reasons, there is a method called `add(reason);`, moreover the constructor accepts an array of reasons to add.
