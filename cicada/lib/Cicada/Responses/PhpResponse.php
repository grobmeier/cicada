<?php
namespace Cicada\Responses;

use Cicada\Templates\Php\PhpTemplate;

class PhpResponse implements Response {
    private $templateFile;
    private $values;

    function __construct($templateFile, $values) {
        $this->templateFile = $templateFile;
        $this->values = $values;
    }

    public function serialize() {
        $template = new PhpTemplate();
        $template->setTemplateFile($this->templateFile);
        $template->assignValues($this->values);
        return $template->serialize();
    }
}