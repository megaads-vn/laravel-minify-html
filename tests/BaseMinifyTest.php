<?php

use Mockery as m;
use Fitztrev\LaravelHtmlMinify\LaravelHtmlMinifyCompiler;

abstract class BaseMinifyTester extends PHPUnit_Framework_TestCase
{
    protected $config;

    public function setUp()
    {
        $this->compiler = new LaravelHtmlMinifyCompiler(
            $this->config,
            m::mock('Illuminate\Filesystem\Filesystem'),
            __DIR__
        );
    }

    /* *** */

    public function testRemoveHtmlComments()
    {
        $string = '<html>
            <body>
                <!-- start content -->
                <p>hello</p> <!-- greet the user -->
                <!--
                    <p>old code that is commented out</p>
                -->
                <!-- end content -->
            </body>
        </html>';
        $expected = '<html> <body> <p>hello</p> </body> </html>';

        $result = $this->compiler->compileString($string);

        if ($this->config['enabled']) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertEquals($string, $result);
        }
    }

    public function testKeepConditionalComments()
    {
        $string = '<html>
            <body>
                <!--[if IE 6]>
                    <p>hello, IE6 user</p>
                <![endif]-->

                <!--[if IE 8]><p>hello, IE8 user</p><![endif]-->
            </body>
        </html>';
        $expected = '<html> <body> <!--[if IE 6]> <p>hello, IE6 user</p> <![endif]--> <!--[if IE 8]><p>hello, IE8 user</p><![endif]--> </body> </html>';

        $result = $this->compiler->compileString($string);

        if ($this->config['enabled']) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertEquals($string, $result);
        }
    }

    /* *** */

    public function testPreTag()
    {
        $string = '<html>
            <body>
                <pre>hello</pre>
            </body>
        </html>';
        $expected = '<html>
            <body>
                <pre>hello</pre>
            </body>
        </html>';

        $result = $this->compiler->compileString($string);
        $this->assertEquals($expected, $result);
    }

    public function testPreTagWithClass()
    {
        $string = '<html>
            <body>
                <pre class="test">hello</pre>
            </body>
        </html>';
        $expected = '<html>
            <body>
                <pre class="test">hello</pre>
            </body>
        </html>';

        $result = $this->compiler->compileString($string);
        $this->assertEquals($expected, $result);
    }

    public function testTextareaTag()
    {
        $string = '<html>
            <body>
                <textarea>hello</textarea>
            </body>
        </html>';
        $expected = '<html>
            <body>
                <textarea>hello</textarea>
            </body>
        </html>';

        $result = $this->compiler->compileString($string);
        $this->assertEquals($expected, $result);
    }

    public function testTextareaTagWithAttributes()
    {
        $string = '<html>
            <body>
                <textarea rows="5" cols="5"">hello</textarea>
            </body>
        </html>';
        $expected = '<html>
            <body>
                <textarea rows="5" cols="5"">hello</textarea>
            </body>
        </html>';

        $result = $this->compiler->compileString($string);
        $this->assertEquals($expected, $result);
    }

    /* *** */

    public function testSingleExternalScriptTag()
    {
        $string = '<html>
            <head>
                <script type="text/javascript" src="script.js"></script>
            </head>
        </html>';
        $expected = '<html> <head> <script type="text/javascript" src="script.js"></script> </head> </html>';

        $result = $this->compiler->compileString($string);

        if ($this->config['enabled']) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertEquals($string, $result);
        }
    }

    public function testSingleExternalScriptTagWithCacheBuster()
    {
        $string = '<html>
            <head>
                <script type="text/javascript" src="script.<?php echo filemtime("script.js"); ?>.js"></script>
            </head>
        </html>';
        $expected = '<html> <head> <script type="text/javascript" src="script.<?php echo filemtime("script.js"); ?>.js"></script> </head> </html>';

        $result = $this->compiler->compileString($string);

        if ($this->config['enabled']) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertEquals($string, $result);
        }
    }

    public function testMultipleExternalScriptTag()
    {
        $string = '<html>
            <head>
                <script type="text/javascript" src="script1.js"></script>
                <script type="text/javascript" src="script2.js"></script>
            </head>
        </html>';
        $expected = '<html> <head> <script type="text/javascript" src="script1.js"></script> <script type="text/javascript" src="script2.js"></script> </head> </html>';

        $result = $this->compiler->compileString($string);

        if ($this->config['enabled']) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertEquals($string, $result);
        }
    }

    public function testExternalAndEmbeddedScriptTag()
    {
        $string = '<html>
            <head>
                <script type="text/javascript" src="script.js"></script>
                <script type="text/javascript">
                    alert("ok");
                </script>
            </head>
        </html>';
        $expected = '<html>
            <head>
                <script type="text/javascript" src="script.js"></script>
                <script type="text/javascript">
                    alert("ok");
                </script>
            </head>
        </html>';

        $result = $this->compiler->compileString($string);
        $this->assertEquals($expected, $result);
    }

    public function testGoogleAdSenseEmbedTag()
    {
        $string = '<html>
            <body>
                <script type="text/javascript"><!--
                    google_ad_client = "ca-pub-XXX";
                    /* faviconit */
                    google_ad_slot = "XXX";
                    google_ad_width = 300;
                    google_ad_height = 600;
                    //-->
                </script>
                <script type="text/javascript"
                    src="//pagead2.googlesyndication.com/pagead/show_ads.js">
                </script>
            </body>
        </html>';

        $expected = '<html>
            <body>
                <script type="text/javascript"><!--
                    google_ad_client = "ca-pub-XXX";
                    /* faviconit */
                    google_ad_slot = "XXX";
                    google_ad_width = 300;
                    google_ad_height = 600;
                    //-->
                </script>
                <script type="text/javascript"
                    src="//pagead2.googlesyndication.com/pagead/show_ads.js">
                </script>
            </body>
        </html>';

        $result = $this->compiler->compileString($string);
        $this->assertEquals($expected, $result);
    }

    public function testEmbeddedScriptTagSingleLine()
    {
        $string = '<html>
            <head>
                <script>alert("ok");</script>
            </head>
        </html>';
        $expected = '<html>
            <head>
                <script>alert("ok");</script>
            </head>
        </html>';

        $result = $this->compiler->compileString($string);
        $this->assertEquals($expected, $result);
    }

    public function testEmbeddedScriptTagMultipleLines()
    {
        $string = '<html>
            <head>
                <script>
                    alert("ok");
                    alert("ok");
                </script>
            </head>
        </html>';
        $expected = '<html>
            <head>
                <script>
                    alert("ok");
                    alert("ok");
                </script>
            </head>
        </html>';

        $result = $this->compiler->compileString($string);
        $this->assertEquals($expected, $result);
    }

    /* *** */

    public function testValueWithoutMultipleSpacesSingleWord()
    {
        $string = '<html>
            <body>
                <form>
                    <input type="submit" value="Submit" />
                </form>
            </body>
        </html>';
        $expected = '<html> <body> <form> <input type="submit" value="Submit" /> </form> </body> </html>';

        $result = $this->compiler->compileString($string);

        if ($this->config['enabled']) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertEquals($string, $result);
        }
    }

    public function testValueWithoutMultipleSpacesSingleWordSingleQuotes()
    {
        $string = '<html>
            <body>
                <form>
                    <input type="submit" value=\'Submit\' />
                </form>
            </body>
        </html>';
        $expected = '<html> <body> <form> <input type="submit" value=\'Submit\' /> </form> </body> </html>';

        $result = $this->compiler->compileString($string);

        if ($this->config['enabled']) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertEquals($string, $result);
        }
    }

    public function testValueWithoutMultipleSpacesMultipleWords()
    {
        $string = '<html>
            <body>
                <form>
                    <input type="submit" value="Add Document" />
                </form>
            </body>
        </html>';
        $expected = '<html> <body> <form> <input type="submit" value="Add Document" /> </form> </body> </html>';

        $result = $this->compiler->compileString($string);

        if ($this->config['enabled']) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertEquals($string, $result);
        }
    }

    public function testValueWithMultipleSpaces()
    {
        $string = '<html>
            <body>
                <form>
                    <input type="submit" value="     Submit     " />
                </form>
            </body>
        </html>';
        $expected = '<html>
            <body>
                <form>
                    <input type="submit" value="     Submit     " />
                </form>
            </body>
        </html>';

        $result = $this->compiler->compileString($string);
        $this->assertEquals($expected, $result);
    }

    public function testValueWithMultipleSpacesSingleQuotes()
    {
        $string = '<html>
            <body>
                <form>
                    <input type="submit" value=\'     Submit     \' />
                </form>
            </body>
        </html>';
        $expected = '<html>
            <body>
                <form>
                    <input type="submit" value=\'     Submit     \' />
                </form>
            </body>
        </html>';

        $result = $this->compiler->compileString($string);
        $this->assertEquals($expected, $result);
    }

    /* *** */

    public function testAllowedHtml()
    {
        $string = '<html>
            <body>
                <p>hello</p>
            </body>
        </html>';
        $expected = '<html> <body> <p>hello</p> </body> </html>';

        $result = $this->compiler->compileString($string);

        if ($this->config['enabled']) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertEquals($string, $result);
        }
    }

    public function testMultipleSpaces()
    {
        $string = '<html>
            <body>
                <p>hello  with     random     spaces</p>
            </body>
        </html>';
        $expected = '<html> <body> <p>hello with random spaces</p> </body> </html>';

        $result = $this->compiler->compileString($string);

        if ($this->config['enabled']) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertEquals($string, $result);
        }
    }

    public function testNoIndentation()
    {
        $string = '<input
type="text"
name="myinput">';
        $expected = '<input type="text" name="myinput">';

        $result = $this->compiler->compileString($string);

        if ($this->config['enabled']) {
            $this->assertEquals($expected, $result);
        } else {
            $this->assertEquals($string, $result);
        }
    }

    public function testSkipMinification()
    {
        $string = '<p>
<!-- skipmin -->
</p>';
        $expected = '<p>
<!-- skipmin -->
</p>';

        $result = $this->compiler->compileString($string);
        $this->assertEquals($expected, $result);
    }

}

class EnabledTester extends BaseMinifyTester
{
    public function __construct()
    {
        $this->config = array(
            'enabled' => true,
            'blade' => array(
                'contentTags' => array('{{', '}}'),
                'escapedContentTags' => array('{{{', '}}}')
            )
        );
    }

    public function testConfigEnabled()
    {
        $this->assertEquals($this->config['enabled'], true);
    }
}

class DisabledTester extends BaseMinifyTester
{
    public function __construct()
    {
        $this->config = array(
            'enabled' => false,
            'blade' => array(
                'contentTags' => array('{{', '}}'),
                'escapedContentTags' => array('{{{', '}}}')
            )
        );
    }

    public function testConfigDisabled()
    {
        $this->assertEquals($this->config['enabled'], false);
    }
}

class DefaultContentTags extends BaseMinifyTester
{
    public function __construct()
    {
        $this->config = array(
            'enabled' => false,
            'blade' => array(
                'contentTags' => array('{{', '}}'),
                'escapedContentTags' => array('{{{', '}}}')
            )
        );
    }

    public function testDefaultContentTags()
    {
        $string = '{{ "hello world" }}';
        $expected = '<?php echo "hello world"; ?>';

        $result = $this->compiler->compileString($string);

        $this->assertEquals($expected, $result);
    }

    public function testDefaultEscapedContentTags()
    {
        $string = '{{{ "hello world" }}}';
        $expected = '<?php echo e("hello world"); ?>';

        $result = $this->compiler->compileString($string);

        $this->assertEquals($expected, $result);
    }
}

class ChangedContentTags extends BaseMinifyTester
{
    public function __construct()
    {
        $this->config = array(
            'enabled' => false,
            'blade' => array(
                'contentTags' => array('[[', ']]'),
                'escapedContentTags' => array('[[[', ']]]')
            )
        );
    }

    public function testChangedContentTags()
    {
        $string = '[[ "hello world" ]]';
        $expected = '<?php echo "hello world"; ?>';

        $result = $this->compiler->compileString($string);

        $this->assertEquals($expected, $result);
    }

    public function testChangedEscapedContentTags()
    {
        $string = '[[[ "hello world" ]]]';
        $expected = '<?php echo e("hello world"); ?>';

        $result = $this->compiler->compileString($string);

        $this->assertEquals($expected, $result);
    }
}
