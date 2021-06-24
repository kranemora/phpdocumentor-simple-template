<?php
declare(strict_types=1);
/**
 * Twig Custom Extensions for phpDocumentor
 *
 * PHP Version 7.2
 *
 * @package    PhpDocumentor
 * @subpackage Plugin\Twig
 * @author     Fernando Pita <kranemora@gmail.comt>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 * @link       https://github.com/kranemora/phpdocumentor-simple-template/
 */

namespace kranemora\Twig\Extension;

use \ArrayIterator;
use phpDocumentor\Descriptor\Collection;
use phpDocumentor\Descriptor\DescriptorAbstract;

/**
 * Custom Extension adding phpDocumentor specific functionality for Twig templates.
 *
 * Additional functions:
 * - descriptor(element): get the type of the element (function, method, constant, property)
 * - highlight(string): Get source code with sintaxis highlight
 *
 * Additional filters:
 * - sortfqsen_desc: Sorts the given objects by their Path (FileDescriptor) or FullyQualifiedStructuralElementName
 * (any other) in a descending fashion
 * - sortfqsen_asc: Sorts the given objects by their Path (FileDescriptor) or FullyQualifiedStructuralElementName
 * (any other) in a ascending fashion
 *
 * @package    PhpDocumentor
 * @subpackage Plugin\Twig
 * @author     Fernando Pita <kranemora@gmail.comt>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 * @link       https://github.com/kranemora/phpdocumentor-simple-template/
 */
class CustomExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * Returns a listing of all functions that this extension adds.
     *
     * This method is automatically used by Twig upon registering this extension (which is done automatically
     * by phpDocumentor) to determine an additional list of functions.
     *
     * @return \Twig_FunctionInterface[]
     */
    public function getFunctions() : array
    {
        return array(
            new \Twig\TwigFunction('descriptor', array($this, 'descriptor')),
            new \Twig\TwigFunction('highlight', array($this, 'highlight'))
        );
    }

    /**
     * Returns a list of all filters that are exposed by this extension.
     *
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters() : array
    {
        return array(
            'sortfqsen' => new \Twig\TwigFilter('sortfqsen_*', array($this, 'sortfqsen')),
        );
    }

    /**
     * Get the type of the element (function, method, constant, property)
     *
     * @param \phpDocumentor\Descriptor\DescriptorAbstract|string $element Element descriptor
     *
     * @return string Type of the element
     */
    public function descriptor($element) : string
    {
        if ($element instanceof DescriptorAbstract ) {
            $className = explode('\\', get_class($element));
            return strtolower(str_replace('Descriptor', '', $className[count($className)-1]));
        } elseif (gettype($element) == 'string') {
            return $element;
        } else {
            return '';
        }
    }

    /**
     * Get source code with sintaxis highlight
     *
     * @param $source String Source code
     *
     * @return string Source code with sintaxis highlight
     */
    public function highlight(string $source) : string
    {
        $highlighter = new \FSHL\Highlighter(
            new \FSHL\Output\Html(),
            \FSHL\Highlighter::OPTION_TAB_INDENT | \FSHL\Highlighter::OPTION_LINE_COUNTER
        );
        $source = $highlighter->highlight($source, new \FSHL\Lexer\Php());
        $sourceLines = explode("\n", $source);

        foreach ($sourceLines as $k => $v) {
            $sourceLines[$k] = str_replace('<span class="line">', '<span id="L-'.($k+1).'" class="line">', $v);
        }
        $source = implode("\n", $sourceLines);

        return $source;
    }

    /**
     * Sorts the given objects by their path (FileDescriptor) or fully qualified structural element name (any other)
     * in the given direction
     *
     * @param string                               $direction  Given direction, ascending or descending
     * @param \phpDocumentor\Descriptor\Collection $collection Object to sort
     *
     * @return \ArrayIterator Object sorted
     */
    public function sortfqsen(string $direction, Collection $collection) : ArrayIterator
    {
        $iterator = $collection->getIterator();
        $iterator->uasort(
            function ($a, $b) use ($direction) {
                if (get_class($a) != 'phpDocumentor\Descriptor\FileDescriptor') {
                    $aElem = strtolower((string)$a->getFullyQualifiedStructuralElementName());
                } else {
                    $aElem = str_replace('\\', '/', strtolower($a->getPath()));
                    if (strpos($aElem, '/') === false) {
                        $aElem = '/'.$aElem;
                    }
                }
                if (get_class($b) != 'phpDocumentor\Descriptor\FileDescriptor') {
                    $bElem = strtolower((string)$b->getFullyQualifiedStructuralElementName());
                } else {
                    $bElem = str_replace('\\', '/', strtolower($b->getPath()));
                    if (strpos($bElem, '/') === false) {
                        $bElem = '/'.$bElem;
                    }
                }
                if ($aElem === $bElem) {
                    return 0;
                }
                if ($direction === 'asc' && $aElem > $bElem || $direction === 'desc' && $aElem < $bElem) {
                    return 1;
                }

                return -1;
            }
        );
        return $iterator;
    }
}
