<?php
/*
 *  Copyright 2013-2014 Christian Grobmeier, Ivan Habunek
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
namespace Cicada\Renderer;

class PhpRenderer
{
    private $templateFolder;
    private $decoratorVariable = 'content';
    private $values;

    public function __construct($templateDir = '.')
    {
        $this->templateFolder = realpath($templateDir).'/';
    }

    public function setTemplateFolder($templateFolder)
    {
        $this->templateFolder = realpath($templateFolder).'/';
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
     * @param $templateFile
     * @param $variables
     * @param $decoratorConfig
     * @return string
     */
    public function render($templateFile, $variables = [], $decoratorConfig = [])
    {
        $decorator = null;
        $decoratorVariable = 'content';

        if (sizeOf($decoratorConfig) > 0) {
            $decorator = $decoratorConfig['file'];
            $decoratorVariable = $decoratorConfig['name'];
        }

        $this->values = (object) $variables;

        ob_start();
        include($this->templateFolder.$templateFile);

        $content = ob_get_clean();

        if ($decorator == null) {
            return $content;
        } else {
            $this->values->$decoratorVariable = $content;
        }

        ob_start();
        include($this->templateFolder.$decorator);

        return ob_get_clean();
    }
}
