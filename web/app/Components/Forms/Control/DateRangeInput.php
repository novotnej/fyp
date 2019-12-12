<?php

namespace App\Components\Forms;

use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;
use Nette\InvalidArgumentException;

class DateRangeInput extends TextInput
{
	
	const DATE_FORMAT = 'j.n.Y';

	const DATE_PATTERN = '([0-9]{1,2})\.\s*([0-9]{1,2})\.\s*([0-9]{4})';
	
	const DELIMITER = ' - ';
	
	/** @var string */
	protected $dateFormat;
	
	/** @var string */
	protected $datePattern;
	
	/** @var string */
	protected $fullPattern;
	
	/** @var string */
	protected $delimiter;

	
	public function __construct($label = NULL, $dateFormat = self::DATE_FORMAT,
		$delimiter = self::DELIMITER, $datePattern = self::DATE_PATTERN, $fullPattern = NULL, $maxLength = 23)
	{
		if($fullPattern === NULL){
			$fullPattern = $datePattern . $delimiter . $datePattern;
		}
		$this->dateFormat = $dateFormat;
		$this->datePattern = $datePattern;
		$this->fullPattern = $fullPattern;
		$this->delimiter = $delimiter;
		
		parent::__construct($label, $maxLength);
//		$this->control->addAttributes(['data-type' => $type]);
		$this->setAttribute('class', "js-daterange");
		
		# pattern not working for validation TODO: fix?
//		$this->addCondition(Form::FILLED)
//			->addRule(Form::PATTERN, $this->fullPattern);
	}
	
	
	/**
	 * @param \DateTime[] $value
	 * @return string
	 */
	private function formatValue(array $value)
	{
		return implode(' - ', array_map(function(\DateTime $item){
			$item->format(self::DATE_FORMAT);
		}, $value));
	}
	
	
	public function validate()
	{
		$value = $this->getValue();
		if ($this->rawValue && !$value) {
			$this->addError('form.format.dateRange');
			return;
		}
		# TODO: allow and check max and min? see DateTimeInput
//		if($this->value){
//			if(isset($this->min) && $this->value < $this->min){
//				$this->addError(sprintf($this->form->getTranslator()->translate('forms.format.dateMin'), $this->formatValue($this->min)));
//				return;
//			}
//			if(isset($this->max) && $this->value > $this->max){
//				$this->addError(sprintf($this->form->getTranslator()->translate('forms.format.dateMin'), $this->formatValue($this->max)));
//				return;
//			}
//		}
		parent::validate();
	}
	
	
	/**
	 * @return \DateTime[]|NULL
	 */
	public function getValue()
	{
		$value = parent::getValue();
		if ($value === '') {
			return NULL;
		}
		return self::parseValue($value);
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
	 * @param string|string[]|\DateTime[] $value
	 * @return \Nette\Forms\Controls\TextBase
	 */
	public function setValue($value)
	{
		if(is_array($value)){
			foreach($value as $i => $item){
				if($item instanceof \DateTime){
					$value[$i] = $item->format($this->dateFormat);
				} elseif(!preg_match($this->datePattern, $item)) {
					throw new InvalidArgumentException("Date $i '$item' is not in format '$this->dateFormat'.");
				}
			}
			$value = implode($this->delimiter, $value);
		} elseif(strlen($value) && !preg_match("/$this->fullPattern/", $value)) {
			throw new InvalidArgumentException("Date range '$value' is not in format '$this->dateFormat$this->delimiter$this->dateFormat'.");
		} elseif(!is_string($value) && $value !== NULL) {
			throw new InvalidArgumentException(__METHOD__ . " expects a string or array of two dates.");
		}
		return parent::setValue($value);
	}


	/**
	 * @param $value
	 * @return \DateTime|NULL
	 */
	protected function parseValue($value)
	{
		try {
			$value = explode($this->delimiter, $value);
			return array_map(function($item){
				return \DateTime::createFromFormat($this->dateFormat, $item);
			}, $value);
		} catch(\Exception $ex) {/* Wrong date */}
		return NULL;
	}

}
