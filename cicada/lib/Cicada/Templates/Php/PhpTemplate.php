<?php
namespace Cicada\Templates\Php;

use Cicada\Templates\Template;

class PhpTemplate implements Template {

    private $templateFile;
    private $values;
    private $originalValues;

    /**
     * @param $templateFile String path to file
     */
    public function setTemplateFile($templateFile) {
        $this->templateFile = $templateFile;
    }

    public function assignValues($values) {
        $this->originalValues = $values;
        // makes the array accessible like an object
        $this->values = (object)$values;
    }

    public function serialize() {
        ob_start();
        include('./templates/'.$this->templateFile);
        return ob_get_clean();
    }
}