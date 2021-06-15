<?php
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
declare(strict_types=1);

namespace kranemora\phpDocumentor\Twig\Extension;

use \ArrayIterator;
use phpDocumentor\Descriptor\Collection;
use phpDocumentor\Descriptor\DescriptorAbstract;

/**
 * Custom Extension adding phpDocumentor specific functionality for Twig templates.
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
     * Sorts the given objects by their path (FileDescriptor) or fully qualified structural element name (any other)
     * in the given direction
     *
     * @param string                               $direction  Given direction, ascending or descending
     * @param \phpDocumentor\Descriptor\Collection $collection Object to sort
     *
     * @return \ArrayIterator Object sorted
     */
    function sortfqsen(string $direction, Collection $collection) : ArrayIterator
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