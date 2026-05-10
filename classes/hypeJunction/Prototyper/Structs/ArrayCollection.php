<?php

namespace hypeJunction\Prototyper\Structs;

/**
 * Uses native PHP array to implement the Collection interface.
 * Borrowed from Elgg core
 */
class ArrayCollection extends \ArrayIterator implements Collection {
	
	/** @var array */
	private $items;
	
	/**
	 * Constructor
	 *
	 * @param array $items The set of items in the collection
	 */
	public function __construct(array $items = []) {
		$this->items = $items;
	}
	
	/**
	 * {@inheritDoc}
	 *
	 * @param mixed $item Item to find
	 * @return bool
	 */
	public function contains($item) {
		return in_array($item, $this->items, true);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return int
	 */
	public function count() {
		return count($this->items);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return mixed
	 */
	public function current() {
		return current($this->items);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param callable $filter Predicate
	 * @return self
	 */
	public function filter(callable $filter) {
		$results = [];
		
		foreach ($this->items as $item) {
			if ($filter($item)) {
				$results[] = $item;
			}
		}
		
		$class = get_class($this);
		return new $class($results);
	}
	
	/**
	 * {@inheritDoc}
	 *
	 * @return mixed
	 */
	public function key() {
		return key($this->items);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param callable $mapper Mapper callback
	 * @return self
	 */
	public function map(callable $mapper) {
		$results = [];
		foreach ($this->items as $item) {
			$results[] = $mapper($item);
		}

		$class = get_class($this);
		return new $class($results);
	}
	
	/**
	 * {@inheritDoc}
	 *
	 * @return mixed
	 */
	public function next() {
		return next($this->items);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function rewind() {
		reset($this->items);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return bool
	 */
	public function valid() {
		return key($this->items) !== null;
	}
}
