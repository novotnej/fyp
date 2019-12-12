<?php

namespace App\Components\Forms;

use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;
use Nette\InvalidArgumentException;
use Nette\Utils\Strings;

class DateTimeInput extends TextInput
{

	const DATETIME = 'date-time',
			DATE = 'date',
			TIME = 'time';
	
	const DATE_FORMAT = 'j.n.Y',
			TIME_FORMAT = 'H:i';

	const DATE_PATTERN = '([0-9]{1,2})\.\s*([0-9]{1,2})\.\s*([0-9]{4})',
			TIME_PATTERN = '([0-9]{1,2})\:([0-9]{2})(?:\:([0-9]{2}))?';

	protected $min;
	
	protected $max;
	
	/** @var string */
	protected $type;
	
	/** @var bool */
	protected $withSeconds;
	
	private static $datetimeClass = '\Nette\Utils\DateTime';

	
	public function __construct($label = NULL, $type = self::DATETIME, $withSeconds = true)
	{
		$maxLength = NULL;
		$pattern = NULL;
		switch ($type) {
			case self::DATETIME:
				$maxLength = 21;
				$pattern = self::DATE_PATTERN . '\s+' . self::TIME_PATTERN;
				break;
			case self::DATE:
				$maxLength = 12;
				$pattern = self::DATE_PATTERN;
				break;
			case self::TIME:
				$maxLength = 8;
				$pattern = self::TIME_PATTERN;
				break;
			default:
				throw new InvalidArgumentException("Wrong type given. ({$type} given)");
		}
		
		$this->type = $type;
		$this->withSeconds = $withSeconds;
		
		parent::__construct($label, $maxLength);
		$this->control->addAttributes(['data-type' => $type]);
		$this->setAttribute('class', $type."-picker");

	}
	
	
	/**
	 * @param \DateTime $min
	 * @return $this
	 */
	public function setMin(\DateTime $min)
	{
		$this->min = $min;
		$this->control->addAttributes(['data-min' => $this->formatValue($min)]);
		return $this;
	}
	
	
	/**
	 * @param \DateTime $max
	 * @return $this
	 */
	public function setMax(\DateTime $max)
	{
		$this->max = $max;
		$this->control->addAttributes(['data-max' => $this->formatValue($max)]);
		return $this;
	}
	
	
	/**
	 * @param \DateTime $value
	 * @return string
	 */
	private function formatValue(\DateTime $value)
	{
		switch ($this->type) {
			case self::DATE:
				return $value->format(self::DATE_FORMAT);
			case self::TIME:
				return $value->format($this->getTimeFormat());
			default:
				return $value->format(self::DATE_FORMAT . ' ' . $this->getTimeFormat());
		}
	}
	
	
	public function validate()
	{
		$value = $this->getValue();
		if ($this->rawValue && !$value) {
			$this->addError('form.format.' . $this->type);
			return;
		}
		if($this->value){
			if(isset($this->min) && $this->value < $this->min){
				$this->addError(sprintf($this->form->getTranslator()->translate('forms.format.dateMin'), $this->formatValue($this->min)));
				return;
			}
			if(isset($this->max) && $this->value > $this->max){
				$this->addError(sprintf($this->form->getTranslator()->translate('forms.format.dateMin'), $this->formatValue($this->max)));
				return;
			}
		}
		parent::validate();
	}
	
	
	/**
	 * @return \DateTime|NULL
	 */
	public function getValue()
	{
		$value = parent::getValue();
		if ($value === '') {
			return NULL;
		}
		return self::parseValue($value, $this->type);
	}
	
	
	/**
	 * @return null|string
	 */
	public function getFormattedValue()
	{
		$value = $this->getValue();
		if (!$value) {
			return NULL;
		}
		return $this->formatValue($value);
	}
	
	
	/**
	 * @return string
	 */
	protected function getTimeFormat()
	{
		return self::TIME_FORMAT . ($this->withSeconds ? ':s' : '');
	}
	
	
	/**
	 * @param $value
	 * @return \Nette\Forms\Controls\TextBase
	 */
	public function setValue($value)
	{
		if ($value instanceof \DateTime) {
			switch ($this->type) {
				case self::DATE:
					$value = $value->format(self::DATE_FORMAT);
					break;
				case self::TIME:
					$value = $value->format($this->getTimeFormat());
					break;
				default:
					$value = $value->format(self::DATE_FORMAT . ' ' . $this->getTimeFormat());
					break;
			}
		}
		return parent::setValue($value);
	}


	/**
	 * @param $value
	 * @param $type
	 * @return \DateTime|NULL
	 */
	protected function parseValue($value, $type)
	{
		try {
			switch ($type) {
				case self::DATE:
					$match = Strings::match($value, '/^\s*' . self::DATE_PATTERN . '\s*$/');
					if ($match && checkdate($match[2], $match[1], $match[3])) {
						return new self::$datetimeClass("{$match[3]}-{$match[2]}-{$match[1]}");
					}
					break;
				case self::TIME:
					if (Strings::match($value, '/^\s*' . self::TIME_PATTERN . '\s*$/')) {
						return new self::$datetimeClass($value);
					}
					break;
				default:
					$match = Strings::match($value, '/^\s*' . self::DATE_PATTERN . '\s+(' . self::TIME_PATTERN . ')\s*$/');
					if ($match && checkdate($match[2], $match[1], $match[3])) {
						return new self::$datetimeClass("{$match[3]}-{$match[2]}-{$match[1]} {$match[4]}");
					}
					break;
			}
		} catch(\Exception $ex) {/* Wrong date */}
		return NULL;
	}

}
