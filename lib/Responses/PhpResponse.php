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
namespace Cicada\Responses;

use Cicada\Templates\Php\PhpTemplate;

class PhpResponse extends AbstractResponse {
    private $templateFile;
    private $decorator;
    private $values;
    private $base;

    function __construct($templateFile, $values = array()) {
        $this->templateFile = $templateFile;
        $this->values = $values;
    }

    public function base($base) {
        $this->base = $base;
        return $this;
    }

    public function decorate($decorator) {
        $this->decorator = $decorator;
        return $this;
    }

    public function values($values) {
        $this->values = $values;
        return $this;
    }

    /**
     * @param mixed $decorator
     * @deprecated use self::decorate($decorator) instead
     */
    public function setDecorator($decorator) {
        $this->decorate($decorator);
    }

    /**
     * @param $base
     * @deprecated use self::values($values) instead
     */
    public function setBase($base) {
        $this->base($base);
    }

    public function serialize() {
        $template = new PhpTemplate();
        $template->setTemplateFile($this->templateFile);
        $template->setDecorator($this->decorator);

        if ($this->base !== null) {
            $template->setBase($this->base);
        }

        $template->assignValues($this->values);
        return $template->serialize();
    }
}