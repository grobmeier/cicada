<?php
/*
 *  Copyright 2013 Christian Grobmeier
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing,
 *  software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific
 *  language governing permissions and limitations under the License.
 */
namespace Cicada\Templates\Php;

use Cicada\Configuration;
use Cicada\Templates\Template;

class PhpTemplate implements Template {
    private $base = "../templates/";
    private $templateFile;
    private $decorator;

    private $values;
    private $originalValues;

    function __construct() {
        /** @var Configuration $configuration */
        $configuration = Configuration::getInstance();
        $base = $configuration->get('cicada.templates.base');
        if ($base != null) {
            $this->base = $base;
        }
    }

    /**
     * @param $templateFile String path to file
     */
    public function setTemplateFile($templateFile) {
        $this->templateFile = $templateFile;
    }

    public function setBase($base) {
        $this->base = $base;
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

    /**
     * Function to be called within a template.
     * Can include other files, like header.
     *
     * @param $path
     */
    public function load($path) {
        include($this->base.$path);
    }

    public function serialize() {
        ob_start();
        include($this->base.$this->templateFile);

        $content = ob_get_clean();

        if($this->decorator == null) {
            return $content;
        }

        ob_start();
        include($this->base.$this->decorator);
        return ob_get_clean();
    }
}