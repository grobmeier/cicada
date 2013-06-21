<?php
namespace Cicada\Templates;

interface Template {

    /**
     * @param $path String path to file
     */
    public function setTemplateFile($path);

    /**
     * @param $values
     * @return mixed
     */
    public function assignValues($values);
}