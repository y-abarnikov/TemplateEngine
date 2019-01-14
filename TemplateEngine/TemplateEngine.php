<?php



class TemplateEngine
{
    /**
     * @var string $layoutName
     *
     * The name of basic layout.
     */
    protected $layoutName = '';

    /**
     * @var array $templateVars
     *
     * Array of variables that will use in layout or template.
     */
    protected $templateVars = [];

    /**
     * @var array $helpers .
     *
     * Array where key=>helper name and value => anonymous function (like function(){ body })
     * or standard php function (like trim) without ().
     */
    protected $helpers = [];

    /**
     * TemplateEngine constructor.
     *
     * @param $layoutName
     * @param array $templateVars
     */
    public function __construct($layoutName, $templateVars = [])
    {
        if (!empty($layoutName)) {
            if (file_exists('../App/Views/Layouts/' . $layoutName . '.php')) {
                $this->layoutName = $layoutName;
            } else {
                $this->layoutName = 'base';
            }
        } else {
            $this->layoutName = 'partial';
        }

        $this->templateVars = $templateVars;
    }

    /**
     * Render a view file
     *
     * @param string $view The view file
     *
     * @return void
     * @throws \Exception
     */
    public function render($view): void
    {
        extract($this->templateVars, EXTR_SKIP);

        $template = '../App/Views/Templates/' . $view . '.php';

        ob_start();

        if (file_exists($template)) {
            require $template;
        } else {
            throw new \Exception($template . 'not found');
        }

        $content = ob_get_clean();

        if (!empty($this->layoutName)) {
            $layout = '../App/Views/Layouts/' . $this->layoutName . '.php';
            if (is_readable($layout)) {
                require $layout;

            } else {
                throw new \Exception($layout . 'not found');
            }
        }
    }

    /**
     * @param string $partialTemplateName
     * @param array $partialVars
     * @throws \Exception
     */
    public function partial(string $partialTemplateName, array $partialVars = []): void
    {
        $data = [];
        if (!empty($partialVars)) {
            $data = $partialVars;
        } elseif (!empty($this->templateVars)) {
            $data = $this->templateVars;
        }

        $tempEngine = new TemplateEngine('', $data);

        $tempEngine->render($partialTemplateName);
    }

    /**
     * @param string $name
     * @param $value
     *
     * Add variable that will be used in layout.
     */
    public function addVariable(string $name, $value): void
    {
        $this->templateVars[$name] = $value;
    }

    /**
     * @param array $variables
     *
     * Add array of layout or partial variables
     */
    public function addArrayVariables(array $variables): void
    {
        array_merge($this->templateVars, $variables);
    }

    /**
     * @param string $name
     *
     * Delete partial or layout variables.
     */
    public function deleteVariable(string $name): void
    {

        if (isset($this->templateVars[$name])) {
            unset($this->templateVars[$name]);
        }
    }

    /**
     * Delete all variables.
     */
    public function deleteAllVariables(): void
    {
        unset($this->templateVars);
    }

    /**
     * @param string $name
     * @param $function
     *
     * Add helper like like $name = now, $function = function(){return  date('m.d.Y');}; ||  date (use without '()')
     * and put string format when you will call the function.
     */
    public function addHelper(string $name, $function): void
    {
        if (!empty($name) && !empty($function)) {
            $this->helpers[$name] = $function;
        }
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed|null
     *
     * Call helper function by searching by name in array helpers.
     */
    public function __call($method, $parameters)
    {
        if (array_key_exists($method, $this->helpers)) {
            if (count($parameters) > 0) {
                return call_user_func_array($this->helpers[$method], $parameters);
            }

            /** @var string $method */
            return $this->helpers[$method]();
        }

        return null;
    }
}