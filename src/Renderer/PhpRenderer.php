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

class PhpRenderer
{
    private $templateFolder = '';
    private $templateFile;
    private $decorator;
    private $decoratorVariable = 'content';

    private $values;
    private $originalValues;

    public function __construct($templateFile, $templateFolder = '.')
    {
        $this->templateFile = $templateFile;
        $this->templateFolder = realpath($templateFolder).'/';
    }

    public function setTemplateFolder($templateFolder)
    {
        $this->templateFolder = realpath($templateFolder).'/';
    }

    /**
     * @param string $decorator the name of the decorator file
     * @param string $variableName the name of the values property in which the nested content is available for the decorator
     */
    public function setDecorator($decorator, $variableName)
    {
        $this->decorator = $decorator;
        $this->decoratorVariable = $variableName;
    }

    public function setValues($values)
    {
        $this->originalValues = $values;
        // makes the array accessible like an object
        $this->values = (object) $values;
    }

    /**
     * Function to be called within a template.
     * Can include other files, like header.
     *
     * @param $path
     */
    public function load($path)
    {
        include($this->templateFolder.$path);
    }

    /**
     * Renders a php template
     *
     * @return string
     */
    public function render()
    {
        ob_start();
        include($this->templateFolder.$this->templateFile);

        $content = ob_get_clean();

        if ($this->decorator == null) {
            return $content;
        } else {
            $contentName = $this->decoratorVariable;
            $this->values->$contentName = $content;
        }

        ob_start();
        include($this->templateFolder.$this->decorator);

        return ob_get_clean();
    }
}
