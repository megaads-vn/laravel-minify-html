<?php namespace MegaAds\LaravelHtmlMinify;

use Illuminate\View\Compilers\BladeCompiler;

class LaravelHtmlMinifyCompiler extends BladeCompiler
{
    private $_config;

    public function __construct($config, $files, $cachePath)
    {
        parent::__construct($files, $cachePath);

        $this->_config = $config;

        // Add Minify to the list of compilers
        if ($this->_config['enabled'] === true) {
            $this->compilers[] = 'Minify';
        }

        // Set Blade contentTags and escapedContentTags
        $this->setContentTags(
            $this->_config['blade']['contentTags'][0],
            $this->_config['blade']['contentTags'][1]
        );

        $this->setEscapedContentTags(
            $this->_config['blade']['escapedContentTags'][0],
            $this->_config['blade']['escapedContentTags'][1]
        );

    }

    /**
    * We'll only compress a view if none of the following conditions are met.
    * 1) <pre> or <textarea> tags
    * 2) Embedded javascript (opening <script> tag not immediately followed
    * by </script>)
    * 3) Value attribute that contains 2 or more adjacent spaces
    *
    * @param string $value the contents of the view file
    *
    * @return bool
    */
    public function shouldMinify($value)
    {
        if (preg_match('/skipmin/', $value)
         || preg_match('/<(pre|textarea)/', $value)
         || preg_match('/<script[^\??>]*>[^<\/script>]/', $value)
         || preg_match('/value=("|\')(.*)([ ]{2,})(.*)("|\')/', $value)
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
    * Compress the HTML output before saving it
    *
    * @param string $value the contents of the view file
    *
    * @return string
    */
    protected function compileMinify($value)
    {
        if ($this->shouldMinify($value)) {
            $re = '%# Collapse whitespace everywhere but in blacklisted elements.
                (?>             # Match all whitespans other than single space.
                [^\S ]\s*     # Either one [\t\r\n\f\v] and zero or more ws,
                | \s{2,}        # or two or more consecutive-any-whitespace.
                ) # Note: The remaining regex consumes no text at all...
                (?=             # Ensure we are not in a blacklist tag.
                [^<]*+        # Either zero or more non-"<" {normal*}
                (?:           # Begin {(special normal*)*} construct
                    <           # or a < starting a non-blacklist tag.
                    (?!/?(?:textarea|pre|script)\b)
                    [^<]*+      # more non-"<" {normal*}
                )*+           # Finish "unrolling-the-loop"
                (?:           # Begin alternation group.
                    <           # Either a blacklist start tag.
                    (?>textarea|pre|script)\b
                | \z          # or end of file.
                )             # End alternation group.
                )  # If we made it here, we are not in a blacklist tag.
                %Six';
            return preg_replace($re, '', $value);
        } else {
            return $value;
        }

    }

}
