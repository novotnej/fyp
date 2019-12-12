<?php
namespace App\Components;

use App\Components\Forms\BaseForm;
use Nette\Application\UI\Control;
use Nextras\Forms\Rendering\Bs3FormRenderer;

class CommonComponent extends Control {
    public function render() {
        $template = $this->template;
        return $template;
    }


    public function createForm() {
        $form = new BaseForm();
        $form->setRenderer(new Bs3FormRenderer());
        return $form;
    }
}