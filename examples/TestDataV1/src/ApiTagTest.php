<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @category   phpDocumentor
 * @package    Transformer
 * @subpackage Unit_tests
 * @author     Mike van Riel <mike.vanriel@naenius.com>
 * @copyright  2010-2018 Mike van Riel / Naenius (http://www.naenius.com)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://phpdoc.org
 */

/**
 * @api
 * @var int
 */
define('BLA', 1);

/**
 * @var int
 */
define('BLA2', 2);

/**
 * Constant description
 *
 * @api
 *
 * @var int
 * @deprecated 1.0.1 Will be removed in 2.0.0
 */
const BLA3 = 1;

/**
 * @var int
 * @deprecated 1.0.1 Will be removed in 2.0.0
 */
const BLA4 = 1;

/**
 * Function description
 *
 * @api
 */
function bla5()
{
}

// FIXME: Write summary for function
function bla6()
{
}

/**
 * Fixture file for the API tests.
 *
 * @category   phpDocumentor
 * @package    Transformer
 * @subpackage Unit_tests
 * @author     Mike van Riel <mike.vanriel@naenius.com>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://phpdoc.org
 * @api
 */
class phpDocumentor_Tests_Data_ApiFixture
{
    /**
     * @api
     * @var int
     */
    const BLA7 = 1;

    /**
     * #var int
     */
    const BLA8 = 1;

    /**
     * Property description
     *
     * @api
     *
     * @var string
     */
    public $bla9 = '1';

    /**
     * @var string
     */
    public $bla10 = '1';

    /**
     * Function description
     *
     * @api
     */
    public function bla11()
    {
    }

    // FIXME: Write summary for method
    public function bla12()
    {
    }
}
