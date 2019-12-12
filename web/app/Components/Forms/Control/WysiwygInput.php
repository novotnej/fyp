<?php

namespace App\Components\Forms;

use Nette\Forms\Controls\TextArea;

class WysiwygInput extends TextArea
{
	/** @var string  */
	protected $wysiwygHtmlClass = 'wysiwyg';

	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->setAttribute('class', $this->wysiwygHtmlClass);
	}

}
