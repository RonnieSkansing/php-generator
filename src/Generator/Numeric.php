<?php
namespace Generator;


class Numeric {


	/**
	*	Returns a infinite fibbo generator
	*
	*	@param $index
	*	@param $limit
	*	@param $step
	*	@return \Generator 
	*/
	protected function createFibboGenerator($incrementing, $limit, $step)
	{
		$index = ($incrementing) ? 1 : -1;

		$Generator = function() use ($incrementing, $index, $limit, $step)
		{
			$fib = $x = 0;

			while(true)
			{
				yield $fib;
				
				// calculate the fibbo seq.
				for($j = 0; true; ++$j, $fib = ($x + $index))
				{
					if( is_infinite($limit) === true && $j < $step )
					{
						$x = $index;
						$index = $fib;	
					}
					else 
					{
						break;
					}
					
				}
			}
		};

		return $Generator();
	}

	/**
	*	Returns a limited fibbo generator
	*
	*	@param $index
	*	@param $limit
	*	@param $step
	*	@return \Generator 
	*/
	protected function createLimitedFibboGenerator($incrementing, $limit, $step)
	{
		$index = ($incrementing) ? 1 : -1;

		$Generator = function() use ($incrementing, $index, $limit, $step)
		{
			$fib = $x = 0;
			while(true)
			{
				if( 	( $incrementing === false && $fib < $limit )
					|| 	( $incrementing === true && $fib > $limit ) )
				{
					break;
				}

				yield $fib;

				// add up for next iteration and take messure for steps
				for($j = 0; $j < $step; ++$j, $fib = ($x + $index))
				{
					$x = $index;
					$index = $fib;		
				}
			}
		};

		return $Generator();
	}

	/**
	*	Returns a generator within specific length.
	*
	*	@param $index
	*	@param $limit
	*	@param $step
	*	@return \Generator
	*/
	protected function createRangeGenerator($index, $limit, $step)
	{
		$incrementing = ($step > 0);

		// if the range is limited, swap vars.
		if( $incrementing === false && is_infinite($limit) === false)
		{
			$temp = $limit;
			$limit = $index;
			$index = $temp;
		}

		$Generator = function() use($index, $limit, $step, $incrementing) 
		{
			for ($i = $index; true; $i += $step)
			{
				if (( $incrementing === true && $i <= $limit) || ($incrementing === false && $i >= $limit))
				{
					yield $i;
				}
				else
				{
					break;
				}
			}
		};

		return $Generator();
	}


	/**
	*	Returns a prime generator
	*
	*	@param int index
	*	@param int|null $limit
	*	@return void
	*/
	public function createPrimeGenerator($index, $limit) 
	{
		// limited range generator
		$Generator = function() use ($index, $limit) 
		{
			if($index === 2)
			{
				yield $index;
				++$index;
			}

			if($index === 3)
			{
				yield $index;
				++$index;
			}

			// if even, add 1.
			if($index % 2 === 0)
			{
				++$index;
			}
			
			// check against odd numbers
			for(; is_null($limit) === true || $index <= $limit; $index += 2)
			{
				// assert against all previous numbers
				for($i = floor(sqrt($index)); $index > $i; ++$i)
				{
					if( $index % $i === 0)
					{
						continue 2;
					}
				}

				yield $index;
			}
		};

		return $Generator();
	}
		

	/** 
	*	Throws Exception if infinite and has invalid limit
	*
	*	Throws exception If limit is not null, and increasing while limit set to 0 or less.
	*	Throws exception if limit is not null, and decreasing while limit set to 0 or more.
	*
	*	@param $increasing bool
	*	@param $limited int|null
	*	@throws \LogicException
	*	@return void
	*/
	public function throwExceptionIfInvalidInfiniteLimit($increasing, $limit) {
		if(is_null($limit) === false)
		{
			if($increasing === false && $limit >= 0)
			{
				throw new \LogicException('Second argument must be lower then 0 in a infinite increasing sequence.');
			}
			elseif($increasing === true && $limit <= 0)
			{
				throw new \LogicException('Second argument must be lower then 0 in a infinite decreasing sequence.');
			}
		}
	}


	/**
	*	Throws Exception if all elements are null
	*
	*	@param mixed $values
	*	@throws LogicException
	*/
	protected function throwExceptionIfAllNulls(array $values)
	{
 		$types = array_map("gettype", $values);
 		$unique_types = array_unique($types);

 		if( count( $unique_types) === 1 && $unique_types[0] === "NULL")
 		{
 			throw new \LogicException('All values are null');
 		}
	}


	/**
	*	Throws Exception if all elements passed is not integer or null
	*
	*	@param array $values
	*	@return InvalidArgumentException
	*/
	protected function throwExceptionIfNotNullOrInt( array $values )
	{
		foreach($values as $value)
		{
			if(is_null($value) === false && is_int($value) === false)
			{
				throw new \InvalidArgumentException('Not a integer or null type.');
			}
		}
	}


	/**
	*	Throws LogicException if step is over 1
	*
	*	@param int $step
	*	@throws LogicException
	*/
	protected function throwExceptionIfInvalidStep($step)
	{
		if($step < 1)
		{
			throw new \LogicException('The step must be 1 or higher.');
		}
	}


	/**
	*	Returns a generator with Fibonacci sequence
	*
	*	getFibonacci(); // 0,1,1,2,3,5 ...
	*	getFibonacci(false); // 0,-1,-1,-3 ...
	*	getFibonacci(true, null, 2); // 0,1,3 ...
	*	getFibonacci(true, 4); // 0,1,1,2,3
	*	getFibonacci(false, -5); // 0,-1,-1,-2,-3,-5
	*
	*	@param bool $increasing
	*	@param null|int $limit
	*	@param int
	*	@throws InvalidArgumentException|LogicException
	*	@return \Generator
	*/
	public function getFibonacci($increasing = true, $limit = null, $step = 1)
	{
		if(is_bool($increasing) === false)
		{
			throw new \InvalidArgumentException('First argument must be a boolean, given ' . gettype($increasing) . '.');
		}

		// Throws InvalidArgumentException
		$this->throwExceptionIfNotNullOrInt( [$limit] );

		// Throws LogicException
		$this->throwExceptionIfInvalidStep($step);

		// Throws LogicException
		$this->throwExceptionIfInvalidInfiniteLimit($increasing, $limit);
		
		// infinite increase
		if($increasing === true && is_null($limit))
		{
			return $this->createFibboGenerator(true, INF, $step);
		}

		// infinite decrease range
		if($increasing === false && is_null($limit))
		{
			return $this->createFibboGenerator(false, -INF, $step);
		}

		// decreasing 
		if($increasing === false)
		{
			return $this->createLimitedFibboGenerator(false, $limit, $step);
		}

		// increasing
		return $this->createLimitedFibboGenerator(true, $limit, $step);
	}


	/**
	*	Returns a generator with selected range
	*
	*	getRange(1); // 1,2,3,4,5 ...
	*	getRange(null, 1); // 1,-1,-2,-3 ...
	*	getRange(10, null, 3); // 10,13,16,19 ...
	*	getRange(1, 5); // 1,2,3,4,5
	*	getRange(5, 1); // 5,4,3,2,1
	*	getRange(10, 3, 3); // 10,7,4
	*
	*	@param int|null $index
	*	@param int|null $limit
	*	@param int $step
	*	@throws InvalidArgumentException|LogicException
	*	@return \Generator
	*/
	public function getRange($index = null, $limit = null, $step = 1)
	{
		// Throws LogicException
		$this->throwExceptionIfAllNulls( [$index, $limit] );
		$this->throwExceptionIfInvalidStep($step);

		// Throws InvalidArgumentException
		$this->throwExceptionIfNotNullOrInt( [$index, $limit] );

		// infinite increase range
		if(is_int($index) && is_null($limit))
		{
			return $this->createRangeGenerator($index, INF, $step);
		}
		// infinite decrease range
		if(is_int($limit) && is_null($index))
		{
			return $this->createRangeGenerator($limit, -INF, -1 * $step);
		}

		// predetermined range
		// decrease
		if($index >= $limit)
		{
			return $this->createRangeGenerator($limit, $index, -1 * $step);
		}

		// increase
		return $this->createRangeGenerator($index, $limit, $step);		
	}


	/**
	*	Returns a prime generator
	*	
	*	The prime generator narrows the result if limit is not a prime
	*	getPrimes(0,20) // 2 .. 19
	*	getPrimes(100) // returns all primes after 100 (or including 100 if it was a prime)
	*
	*	@param $index
	*	@param $limit
	*	@throws InvalidArgumentException|LogicException
	*	@return \Generator
	*/
	public function getPrimes($index = 2, $limit = null) 
	{
		// throws InvalidArgumentException
		$this->throwExceptionIfNotNullOrInt( [$index, $limit] );

		if( $index < 0)
		{
			throw new \LogicException('The range is too low. Index must at least be 0.');
		}

		if( is_null($limit) === false && $limit <= $index )
		{
			throw new \LogicException('The limit must be larger then the index.');
		}

		// narrowing
		if(is_null($limit) === false && $index < 2)
		{
			$index = 2;
		}

		return $this->createPrimeGenerator($index, $limit);
	}
}