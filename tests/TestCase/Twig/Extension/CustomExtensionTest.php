<?php
/**
 * Twig Custom Extension Tests for phpDocumentor
 *
 * PHP Version 7.2
 *
 * @package    Test
 * @subpackage TestCase\Plugin\Twig
 * @author     Fernando Pita <kranemora@gmail.comt>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 * @link       https://github.com/kranemora/phpdocumentor-simple-template/
 */
declare(strict_types=1);

namespace kranemora\Twig\Extension;

use kranemora\Twig\Extension\CustomExtension;
use phpDocumentor\Descriptor\Collection;
use phpDocumentor\Descriptor\FileDescriptor;
use phpDocumentor\Descriptor\PackageDescriptor;
use phpDocumentor\Descriptor\Validation\Error;
use phpDocumentor\Reflection\Fqsen;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use PHPUnit\Framework\TestCase;

/**
 * Twig Custom Extension Tests for phpDocumentor
 *
 * @package    Test
 * @subpackage TestCase\Plugin\Twig
 * @author     Fernando Pita <kranemora@gmail.comt>
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 * @link       https://github.com/kranemora/phpdocumentor-simple-template/
 */
class CustomExtensionTest extends TestCase
{
    /**
     * Test add extension.
     *
     * @return void
     */
    public function testAddExtension() : void
    {
        $twig = new Environment($this->createMock('\Twig\Loader\LoaderInterface'));
        $twig->addExtension(new CustomExtension());
        $this->assertArrayHasKey('descriptor', $twig->getFunctions());
        $this->assertArrayHasKey('highlight', $twig->getFunctions());
        $this->assertArrayHasKey('sortfqsen_*', $twig->getFilters());
    }

    /**
     * Test descriptor function.
     *
     * @return void
     */
    public function testDescriptorFunction() : void
    {
        $loader = new ArrayLoader(
            [
            'func_descriptor' => '{{ descriptor(element) }}'
            ]
        );

        $twig = new Environment($loader);
        $twig->addExtension(new CustomExtension());

        $descriptor = new \phpDocumentor\Descriptor\ConstantDescriptor();
        $descriptor->setNamespace('\My\Namespace');
        $descriptor->setName('CONSTANT');

        $this->assertEquals('constant', $twig->render('func_descriptor', ['element' => $descriptor]));
        $this->assertEquals('constant', $twig->render('func_descriptor', ['element' => 'constant']));
        $this->assertEquals('', $twig->render('func_descriptor', ['element' => null]));
    }

    /**
     * Test highlight function.
     *
     * @return void
     */
    public function testHighlighFunction() : void
    {
        $loader = new ArrayLoader(
            [
            'func_highlight' => '{{ highlight("<?php echo \"Hello world!\"; ?>")|raw }}'
            ]
        );

        $twig = new Environment($loader);
        $twig->addExtension(new CustomExtension());

        $expected = '<span id="L-1" class="line">1: </span><span class="xlang">&lt;?php</span> '
            .'<span class="php-keyword1">echo</span> <span class="php-quote">&quot;Hello world!&quot;</span>; '
            .'<span class="xlang">?&gt;</span>';
        $this->assertEquals($expected, $twig->render('func_highlight'));
    }

    /**
     * Test sort no file collection by fully qualified structural element name
     *
     * @return void
     */
    public function testSortfqsenNoFileFilter() : void
    {
        $twig = new Environment($this->createMock('\Twig\Loader\LoaderInterface'));

        $collection = new \phpDocumentor\Descriptor\Collection();

        $rootPackage = new PackageDescriptor();
        $rootPackage->setFullyQualifiedStructuralElementName(new Fqsen('\\'));
        $eFooPackage = new PackageDescriptor();
        $eFooPackage->setFullyQualifiedStructuralElementName(new Fqsen('\\EFoo'));
        $aFooPackage = new PackageDescriptor();
        $aFooPackage->setFullyQualifiedStructuralElementName(new Fqsen('\\AFoo'));
        $zFooPackage = new PackageDescriptor();
        $zFooPackage->setFullyQualifiedStructuralElementName(new Fqsen('\\ZFoo'));
        $wFooPackage = new PackageDescriptor();
        $wFooPackage->setFullyQualifiedStructuralElementName(new Fqsen('\\WFoo'));
        $duplicateWFooPackage = new PackageDescriptor();
        $duplicateWFooPackage->setFullyQualifiedStructuralElementName(new Fqsen('\\WFoo'));

        $collection->set('\\', $rootPackage);
        $collection->set('\\EFoo', $eFooPackage);
        $collection->set('\\AFoo', $aFooPackage);
        $collection->set('\\ZFoo', $zFooPackage);
        $collection->set('\\WFoo', $wFooPackage);
        $collection->set('\\DuplicateWFoo', $wFooPackage);

        $extension = new CustomExtension();

        $collectionKeys = array_keys(iterator_to_array($extension->sortfqsen('asc', $collection)));
        $expected = ['\\', '\\AFoo', '\\EFoo', '\\WFoo', '\\DuplicateWFoo', '\\ZFoo'];
        $this->assertEquals(json_encode($expected), json_encode($collectionKeys));

        $collectionKeys = array_keys(iterator_to_array($extension->sortfqsen('desc', $collection)));
        $expected = ['\\ZFoo', '\\WFoo', '\\DuplicateWFoo', '\\EFoo', '\\AFoo', '\\'];
        $this->assertEquals(json_encode($expected), json_encode($collectionKeys));
    }

    /**
     * Test sort file collection by fully qualified structural element name
     *
     * @return void
     */
    public function testSortfqsenFileFilter() : void
    {
        $twig = new Environment($this->createMock('\Twig\Loader\LoaderInterface'));

        $collection = new \phpDocumentor\Descriptor\Collection();

        $eFooFile = new FileDescriptor(md5('eFoo.php'));
        $eFooFile->setPath('eFoo.php');
        $aFooFile = new FileDescriptor(md5('aFoo.php'));
        $aFooFile->setPath('aFoo.php');
        $fileLevelAFooFile = new FileDescriptor(md5('file-level\\aFoo.php'));
        $fileLevelAFooFile->setPath('file-level\\aFoo.php');
        $zFooFile = new FileDescriptor(md5('zFoo.php'));
        $zFooFile->setPath('zFoo.php');
        $wFooFile = new FileDescriptor(md5('wFoo.php'));
        $wFooFile->setPath('wFoo.php');
        $fileLevelWFooFile = new FileDescriptor(md5('file-level\\wFoo.php'));
        $fileLevelWFooFile->setPath('file-level\\wFoo.php');
        $duplicateWFooFile = new FileDescriptor(md5('duplicateWFoo.php'));
        $duplicateWFooFile->setPath('wFoo.php');

        $collection->set('eFoo.php', $eFooFile);
        $collection->set('aFoo.php', $aFooFile);
        $collection->set('file-level\aFoo.php', $fileLevelAFooFile);
        $collection->set('zFoo.php', $zFooFile);
        $collection->set('wFoo.php', $wFooFile);
        $collection->set('file-level\wFoo.php', $fileLevelWFooFile);
        $collection->set('duplicateWFoo.php', $duplicateWFooFile);

        $extension = new CustomExtension();

        $collectionKeys = array_keys(iterator_to_array($extension->sortfqsen('asc', $collection)));
        $expected = [
            'aFoo.php',
            'eFoo.php',
            'wFoo.php',
            'duplicateWFoo.php',
            'zFoo.php',
            'file-level\aFoo.php',
            'file-level\wFoo.php'
        ];
        $this->assertEquals(json_encode($expected), json_encode($collectionKeys));

        $collectionKeys = array_keys(iterator_to_array($extension->sortfqsen('desc', $collection)));
        $expected = [
            'file-level\wFoo.php',
            'file-level\aFoo.php',
            'zFoo.php',
            'wFoo.php',
            'duplicateWFoo.php',
            'eFoo.php',
            'aFoo.php'
        ];
        $this->assertEquals(json_encode($expected), json_encode($collectionKeys));
    }
}
