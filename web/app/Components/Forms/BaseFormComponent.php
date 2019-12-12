<?php
namespace MediPoint\Components;

use App\Components\Forms\BaseForm;
use App\Services\OrmService;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use Nette\Utils\ArrayHash;
use Nette\Localization\ITranslator;
use Nette\Utils\Callback;

abstract class BaseFormComponent extends Control
{

    /** @var callable[] */
    public $onSuccess = [];

    /** @var bool */
    protected $successSuppressed = FALSE;

    /** @var IFormRendererComponentFactory */
    private $formRendererFactory;

    /** @var OrmService */
    protected $orm;

    /** @var ITranslator */
    protected $translator;

    /** @var int count of onSuccess callbacks bound by addOnSuccess method */
    protected $onSuccessCounter = 0;

    /** @var string|null */
    protected $renderStyle = NULL;

    /** @var array  */
    protected $options = [];


    public function __construct(OrmService $orm, IFormRendererComponentFactory $formRendererFactory,
                                ITranslator $translator, IContainer $parent = NULL, $name = NULL)
    {
        parent::__construct();
        $this->formRendererFactory = $formRendererFactory;
        $this->orm = $orm;
        $this->translator = $translator;
    }

    public function render($options = [])
    {
        if(!$options){
            $options = $this->options;
        }
        if(isset($this->renderStyle)){# TODO: review
            Callback::invokeArgs([$this, 'render' . ucfirst($this->renderStyle)], [$options]);
        } else {
            /** @var FormRendererComponent $formRenderer */
            $formRenderer = $this['formRenderer'];
            $formRenderer->render($this['form'], $options); # no latte template required
        }
    }

    public function renderInline($options = [])
    {
        if(!$options){
            $options = $this->options;
        }
        /** @var FormRendererComponent $formRenderer */
        $formRenderer = $this['formRenderer'];
        $formRenderer->renderInline($this['form'], $options); # no latte template required
    }

    public function renderVertical($options = [])
    {
        if(!$options){
            $options = $this->options;
        }
        /** @var FormRendererComponent $formRenderer */
        $formRenderer = $this['formRenderer'];
        $formRenderer->renderVertical($this['form'], $options); # no latte template required
    }

    public function renderVerticalGroups($options = [])
    {
        if(!$options){
            $options = $this->options;
        }
        /** @var FormRendererComponent $formRenderer */
        $formRenderer = $this['formRenderer'];
        $formRenderer->renderVerticalGroups($this['form'], $options); # no latte template required
    }

    public function renderGeneratedTasks($options = [])
    {
        if(!$options){
            $options = $this->options;
        }
        /** @var FormRendererComponent $formRenderer */
        $formRenderer = $this['formRenderer'];
        $formRenderer->renderGeneratedTasks($this['form'], $options); # no latte template required
    }


    /**
     * @param string $style
     */
    public function setRenderStyle($style)
    {
        $this->renderStyle = $style;
    }


    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }


    /**
     * @param string $key
     * @param mixed $option
     */
    public function setOption($key, $option)
    {
        $this->options[$key] = $option;
    }

    /**
     * @return Form
     */
    protected function createComponentForm()
    {
        $form = new BaseForm;
        $form->setTranslator($this->translator);
        $this->initialize($form);
        $this->loadDefaults($form);

        $form->onSuccess[] = [$this, 'formSuccess'];
        $form->onSuccess[] = function (Form $form, ArrayHash $values) {
            if ($this->successSuppressed) {
                return;
            }
            foreach ($this->onSuccess as $callback) {
                $callback($form, $values);
            }
        };

        return $form;
    }

    /**
     * @return FormRendererComponent
     */
    protected function createComponentFormRenderer()
    {
        return $this->formRendererFactory->create();
    }

    /**
     * @return FormErrorsComponent
     */
    protected function createComponentFormErrors()
    {
        return new FormErrorsComponent();
    }


    /**
     * @param callable $callback
     * @return self
     */
    public function addOnSuccess($callback)
    {
        $this->onSuccess[] = $callback;
        $this->onSuccessCounter += 1;
        return $this;
    }


    /**
     * @param array $defaults
     * @param bool $erase
     */
    public function setDefaults(array $defaults, $erase = FALSE)
    {
        /** @var Form $form */
        $form = $this['form'];
        $form->setDefaultsSafe($defaults, $erase);
    }


    /**
     * For dial forms.
     * @return string
     */
    public function getEntityDomain()
    {
        return 'entity.' . StringUtils::clean($this->getPureName());
    }


    /**
     * For dial forms.
     * @return string
     */
    public function getFormDomain()
    {
        return 'form.' . StringUtils::clean($this->getPureName());
    }


    /**
     * Returns class name without namespace and 'Form' suffix
     * @return string
     */
    public function getPureName()
    {
        $reflection = new \ReflectionClass(static::class);
        $class = $reflection->getShortName();
        $suffix = 'Form';

        return strpos($class, $suffix) > 0
            ? StringUtils::cutEnd($class, $suffix)
            : $class;
    }


    /**
     * For BaseEntityFormComponent to be overridden.
     * @param BaseForm $form
     */
    protected function loadDefaults(BaseForm $form){}

    /**
     * For BaseEntityFormComponent to be overridden.
     * @param BaseForm $form
     */
    abstract protected function initialize(BaseForm $form);


    /**
     * For BaseEntityFormComponent to be overridden.
     * @param BaseForm $form
     * @param ArrayHash $values
     * @return
     */
    abstract public function formSuccess(BaseForm $form, ArrayHash $values);

}
