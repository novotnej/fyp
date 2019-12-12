<?php

namespace App\Components\Forms;

use Nette\Application\UI\Form as NetteForm;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\SubmitButton;

class BaseForm extends NetteForm {

	/**
	 * @param string $name
	 * @param string $label
	 * @param string $type
	 * @param boolean $withSeconds
	 * @return DateTimeInput
	 */
	public function addDateTime($name, $label = NULL, $type = DateTimeInput::DATETIME, $withSeconds = TRUE) {
		return $this[$name] = new DateTimeInput($label, $type, $withSeconds);
	}
	

	/**
	 * @param string $name
	 * @param string $label
	 * @return DateTimeInput
	 */
	public function addDate($name, $label = NULL) {
		return $this->addDateTime($name, $label, DateTimeInput::DATE);
	}
	
	
	/**
	 * @param string $name
	 * @param string $label
	 * @param string $dateFormat
	 * @param string $delimiter
	 * @param string $datePattern
	 * @param string|null $fullPattern
	 * @param int $maxLength
	 * @return DateRangeInput
	 */
	public function addDateRange($name, $label = NULL, $dateFormat = DateRangeInput::DATE_FORMAT,
		$delimiter = DateRangeInput::DELIMITER, $datePattern = DateRangeInput::DATE_PATTERN, $fullPattern = NULL, $maxLength = 23) {
		return $this[$name] = new DateRangeInput($label, $dateFormat, $delimiter, $datePattern, $fullPattern, $maxLength);
	}
	

	/**
	 * @param string $name
	 * @param string $label
	 * @param array|\Traversable $entities
	 * @param string|callable $nameProperty
	 * @param string|callable $keyProperty
	 * @param integer $size
	 * @return EntitySelectBox
	 */
	public function addEntitySelect($name, $label = NULL, $entities = NULL, $nameProperty = NULL, $keyProperty = 'id', $size = NULL) {
		$control = new EntitySelectBox($label, $entities, $nameProperty, $keyProperty);
		if ($size > 1) {
			$control->setAttribute('size', (int) $size);
		}
		return $this[$name] = $control;
	}

	/**
	 * Adds naming container to the form.
	 * @param  string|int
	 * @return self
	 */
	public function addContainerX($name) {
		$control = new self;
		$control->currentGroup = $this->currentGroup;
		if ($this->currentGroup !== NULL) {
			$this->currentGroup->add($control);
		}
		return $this[$name] = $control;
	}


	/**
	 * @param string $name
	 * @param string $label
	 * @param array|\Traversable $entites
	 * @param string|callable $nameProperty
	 * @param string|callable $keyProperty
	 * @param integer $size
	 * @return MultiEntitySelectBox
	 */
	public function addMultiEntitySelect($name, $label = NULL, $entites = NULL, $nameProperty = NULL, $keyProperty = 'id', $size = NULL) {
		$control = new MultiEntitySelectBox($label, $entites, $nameProperty, $keyProperty);
		if ($size > 1) {
			$control->setAttribute('size', (int) $size);
		}
		return $this[$name] = $control;
	}
	
	
	/**
	 * @param string $name
	 * @param string $label
	 * @return WysiwygInput
	 */
	public function addWysiwyg($name, $label = NULL) {
		return $this[$name] = new WysiwygInput($label);
	}

	
	public function setDefaultsSafe($values, $erase = FALSE) {
		if (!$this->isAnchored() || !$this->isSubmitted()) {
			$this->setValuesSafe($values, $erase);
		}
		return $this;
		
	}

	public function setValuesSafe($values, $erase = FALSE) {
		foreach ($this->getComponents() as $name => $control) {
			if ($control instanceof \Nette\Forms\IControl) {
				if (array_key_exists($name, $values)) {
					try {
						$control->setValue($values[$name]);
					} catch (\Nette\InvalidArgumentException $ex) {
						// skip
					}
				} elseif ($erase) {
					$control->setValue(NULL);
				}
			} elseif ($control instanceof self) {
				if (array_key_exists($name, $values)) {
					try {
						$control->setValues($values[$name], $erase);
					} catch (\Nette\InvalidArgumentException $ex) {
						// skip
					}
				} elseif ($erase) {
					$control->setValues(array(), $erase);
				}
			}
		}
		return $this;
	}

	public function setAjaxProcessing() {
		$this->getElementPrototype()->addAttributes(["class" => "ajax"]);
	}

	public function getSubmitButtonsStack() {
		$buttonStack = [];
		foreach ($this->getControls() as $control){
			if($control instanceof Button || $control instanceof SubmitButton){
				$buttonStack[] = $control;
			}
		}
		return $buttonStack;
	}
}
