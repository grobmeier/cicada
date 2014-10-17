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
namespace Cicada;

class ExceptionDebugWrapper
{
    private $exception;
    private $previous;

    private $statusCode = 500;

    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;

        if (($previous = $exception->getPrevious()) !== null) {
            $this->previous = new static($previous);
        }

        // TODO Retrieve valuable information that the exception could
        //      have. For example, if it's an HttpException could be a
        //      not found exception and the $statusCode should change.
    }

    public function getMessage()
    {
        return $this->exception->getMessage();
    }

    public function getCode()
    {
        return $this->exception->getCode();
    }

    public function getFile()
    {
        return $this->exception->getFile();
    }

    public function getLine()
    {
        return $this->exception->getLine();
    }

    public function getTrace()
    {
        return $this->exception->getTrace();
    }

    public function getTraceAsString()
    {
        return $this->exception->getTraceAsString();
    }

    public function getPrevious()
    {
        return $this->previous;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function toHTML()
    {
        return <<<EOF
<!DOCTYPE html>
<html>
 <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$this->getMessage()} - Error {$this->getStatusCode()}</title>
  <style>
    body { background-color: #F0F0F0; color: #333; margin: 0; padding: 0 10px; }
    h1 { background-color: #CC9999; border-bottom: 1px solid #CCC; font-size: 1.5em; font-weight: normal; margin: 0 -10px; padding: 10px; }
    h1 small { font-size: 0.6em; display: block; }
    h2 { font-size: 1.2em; font-weight: normal; margin: 10px; }
    article { background-color: #FFF; border: 1px solid #999; margin: 10px 0; position: relative; }
    article button { position: absolute; top: 10px; right: 10px; }
    li { margin: 5px 0; }
    li pre { margin: 0; }
    address { font-size: 0.75em; }
    button { background-color: #FFF; border-style: none; }
  </style>
 </head>
 <body>
  <h1>{$this->getMessage()}
   <small>
    {$this->formatClassName()} in {$this->getFile()} at line {$this->getLine()}
   </small>
  </h1>
{$this->toHTMLException()}
  <script type="text/javascript">
   (function(d) {
    var i, l, b, a = d.getElementsByTagName('article');
    for (i = 0; i < a.length; i++) {
     l = a[i].getElementsByTagName('ol')[0];
     l.style.display = 'none';
     b = d.createElement('button');
     b.innerHTML = '▶';
     b.addEventListener('click', (function(el, bt) {
      return function() {
       el.style.display = (el.style.display == 'none') ? '' : 'none';
       bt.innerHTML = (bt.innerHTML == '▶') ? '▼' : '▶';
      };
     })(l, b));
     a[i].appendChild(b);
    }
   })(this.document);
  </script>
 </body>
</html>
EOF;
    }

    public function toHTMLException()
    {
        $list = '';

        foreach ($this->exception->getTrace() as $trace) {
            $class = '';
            $line = '';
            $args = '';

            if (isset($trace['class']) && isset($trace['type'])) {
                $class = $this->formatClassName($trace['class']) . $trace['type'];
            }

            if (isset($trace['file']) && isset($trace['line'])) {
                $line = "in {$trace['file']} at line {$trace['line']}";
            }

            if (isset($trace['args'])) {
                $args = implode(', ', $this->formatArguments($trace['args']));
            }

            $list .= <<<EOF

    <li>
     <pre><strong>{$class}{$trace['function']}</strong>($args)</pre>
     <address>$line</address>
    </li>
EOF;
        }

        $previous = '';
        if ($this->getPrevious() !== null) {
            $previous = $this->getPrevious()->toHTMLException();
        }

        return <<<EOF
  <article>
   <h2>{$this->formatClassName()}: {$this->getMessage()}</h2>
   <ol>{$list}
   </ol>
  </article>
{$previous}
EOF;
    }

    protected function formatClassName($class = null)
    {
        if ($class === null) {
            $class = get_class($this->exception);
        }

        $className = ltrim(strrchr($class, '\\'), '\\');

        if (empty($className)) {
            return $class;
        }

        return "<abbr title=\"$class\">$className</abbr>";
    }

    protected function formatArguments(array $arguments)
    {
        $args = [];

        foreach ($arguments as $key => $argument) {
            if (is_object($argument)) {
                if (!method_exists($argument, '__debugInfo')) {
                    $args[] = $this->formatClassName(
                        get_class($argument)
                    );
                    continue;
                }
                $argument = $arg->__debugInfo();
            }

            switch (gettype($argument)) {
                case 'integer':
                case 'double':
                    $args[] = $argument;
                    break;

                case 'string':
                    $args[] = '"' . $argument . '"';
                    break;

                case 'boolean':
                    $args[] = $argument ? 'true' : 'false';
                    break;

                case 'array':
                    if (empty($argument)) {
                        $args[] = '[]';
                        break;
                    }

                    $keys = array_keys($argument);
                    $array = $this->formatArguments($argument);

                    if (!is_int(current($keys))) {
                        $array = array_map(function($key, $value) {
                            return "\"$key\"=>$value";
                        }, $keys, $array);

                        $args[] = '{' . implode(', ', $array) . '}';
                        break;
                    }

                    $args[] = '[' . implode(', ', $array) . ']';
                    break;

                default:
                    $args[] = gettype($argument);
            }
        }

        return $args;
    }
}