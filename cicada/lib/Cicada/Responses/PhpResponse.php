<?php
namespace Cicada\Responses;

use Cicada\Templates\Php\PhpTemplate;

class PhpResponse extends AbstractResponse {
    private $templateFile;
    private $decorator;
    private $values;

    function __construct($templateFile, $values = array()) {
        $this->templateFile = $templateFile;
        $this->values = $values;
    }

    /**
     * @param mixed $decorator
     */
    public function setDecorator($decorator) {
        $this->decorator = $decorator;
    }

    public function serialize() {
        $template = new PhpTemplate();
        $template->setTemplateFile($this->templateFile);
        $template->setDecorator($this->decorator);
        $template->assignValues($this->values);
        return $template->serialize();
    }
}