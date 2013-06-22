<?php
namespace Cicada\Templates\Php;

use Cicada\Templates\Template;

class PhpTemplate implements Template {

    private $templateFile;
    private $decorator;

    private $values;
    private $originalValues;

    /**
     * @param $templateFile String path to file
     */
    public function setTemplateFile($templateFile) {
        $this->templateFile = $templateFile;
    }

    /**
     * @param mixed $decorator
     */
    public function setDecorator($decorator) {
        $this->decorator = $decorator;
    }

    public function assignValues($values) {
        $this->originalValues = $values;
        // makes the array accessible like an object
        $this->values = (object)$values;
    }

    public function load($path) {
        include('./templates/'.$path);
    }

    public function serialize() {
        ob_start();
        include('./templates/'.$this->templateFile);

        $content = ob_get_clean();

        if($this->decorator == null) {
            return $content;
        }

        ob_start();
        include('./templates/'.$this->decorator);
        return ob_get_clean();
    }
}