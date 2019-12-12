<?php

namespace App\Components\Forms;

use App\Model\CommonModel;
use App\Repositories\CommonRepository;
use BePositive\InvalidArgumentException;
use BePositive\Model\Utils;
use DateTime;
use MediPoint\Model\BaseEntity;
use MediPoint\Model\BaseRepository;
use Nette\Forms\Controls\ChoiceControl;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\SelectBox;
use Traversable;

class MultiEntitySelectBox extends MultiSelectBox
{

	/** @var array|Traversable */
	private $entities;
	
	/** @var string  */
	private $key;
	
	
	public function __construct($label = NULL, $entities = NULL, $name = NULL, $key = 'id')
	{
		$this->key = $key;
		$this->entities = [];
		$items = NULL;
		
		if ($entities !== NULL) {
			if ($entities instanceof CommonRepository) {
				$entities = $entities->findAll();
			} elseif (!is_array($entities) && !($entities instanceof Traversable)) {
				throw new \Nette\InvalidArgumentException('Property $entities must be null, array or Traversable. '
					. get_class($entities) . ' given.');
			}
			$items = [];
			foreach ($entities as $entity) {
				$keyVal = $this->getValueFromEntity($entity, $key);
				$this->entities[$keyVal] = $entity;
				$items[$keyVal] = $this->getValueFromEntity($entity, $name);
			}
		}
		parent::__construct($label, $items);
	}
	
	
	/**
	 * @param CommonModel $entity
	 * @param string|callable $property
	 * @return DateTime|mixed|string
	 */
	private function getValueFromEntity(CommonModel $entity, $property)
	{
		if ($property === NULL) {
			return (string) $entity;
		}
		if (is_string($property)) {
			return $entity->{$property};
		}
		return $property($entity);
	}
	
	
	/**
	 * @return mixed|null
	 */
	public function getValue()
	{
		$ids = parent::getValue();
		$result = [];
		foreach($ids as $id){
			$result[] = $id && array_key_exists($id, $this->entities) ? $this->entities[$id] : NULL;
		}
		return $result;
	}
	
	
	/**
	 * @return array|Traversable
	 */
	public function getEntities()
	{
		return $this->entities;
	}
	
	
	/**
	 * @param $value
	 * @return ChoiceControl
	 */
	public function setValue($value)
	{
		if ($value instanceof CommonModel) {
			$value = $this->getValueFromEntity($value, $this->key);
		}
		return parent::setValue($value);
	}

}
