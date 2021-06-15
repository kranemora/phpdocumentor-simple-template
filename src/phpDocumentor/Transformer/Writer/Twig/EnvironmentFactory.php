<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Transformer\Writer\Twig;

use League\CommonMark\MarkdownConverterInterface;
use phpDocumentor\Descriptor\ProjectDescriptor;
use phpDocumentor\Guides\Twig\TocExtension;
use phpDocumentor\Path;
use phpDocumentor\Transformer\Transformation;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use function ltrim;

class EnvironmentFactory
{
    /** @var LinkRenderer */
    private $renderer;

    /** @var ?Path */
    private $templateOverridesAt;

    /** @var TocExtension */
    private $tocExtension;

    /** @var MarkdownConverterInterface */
    private $markDownConverter;

    public function __construct(
        LinkRenderer $renderer,
        TocExtension $tocExtension,
        MarkdownConverterInterface $markDownConverter
    ) {
        $this->renderer = $renderer;
        $this->tocExtension = $tocExtension;
        $this->markDownConverter = $markDownConverter;
    }

    public function withTemplateOverridesAt(Path $path) : void
    {
        $this->templateOverridesAt = $path;
    }

    public function create(
        ProjectDescriptor $project,
        Transformation $transformation,
        string $destination
    ) : Environment {
        $mountManager = $transformation->template()->files();

        $loaders = [];
        if ($this->templateOverridesAt instanceof Path) {
            $loaders[] = new FilesystemLoader([(string) $this->templateOverridesAt]);
        }

        $loaders[] = new FlySystemLoader($mountManager->getFilesystem('template'), '', 'base');
        $loaders[] = new FlySystemLoader($mountManager->getFilesystem('templates'));

        $env = new Environment(new ChainLoader($loaders));

        $this->addPhpDocumentorExtension($project, $destination, $env);
        $env->addExtension($this->tocExtension);

        // Hack to enable debug from config file
        //$this->enableDebug($env);
        $this->enableDebug($transformation, $env);

        // Hack to enable custom extension from config file
        $this->enableExtension($transformation, $env);

        return $env;
    }

    /**
     * Adds the phpDocumentor base extension to the Twig Environment.
     */
    private function addPhpDocumentorExtension(
        ProjectDescriptor $project,
        string $path,
        Environment $twigEnvironment
    ) : void {
        $extension = new Extension($project, $this->markDownConverter, $this->renderer);
        $extension->setDestination(ltrim($path, '/\\'));
        $twigEnvironment->addExtension($extension);
    }

    /**
     * Hack to enable debug from config file
     * private function enableDebug(Environment $twigEnvironment) : void
     * {
     *     $twigEnvironment->setCache(false);
     *     $twigEnvironment->enableDebug();
     *     $twigEnvironment->enableAutoReload();
     *     $twigEnvironment->addExtension(new DebugExtension());
     * }
     */
    private function enableDebug(Transformation $transformation, Environment $twigEnvironment) : void
    {
        $debugParameter = $transformation->getParameter('twig-debug');
        $isDebug = $debugParameter ? $debugParameter->value() : false;
        if ($isDebug === 'true') {
            $twigEnvironment->setCache(false);
            $twigEnvironment->enableDebug();
            $twigEnvironment->enableAutoReload();
            $twigEnvironment->addExtension(new DebugExtension());
        }
    }

    /**
     * Hack to enable custom extension from config file
     */
    private function enableExtension(Transformation $transformation, Environment $twigEnvironment) : void
    {
        foreach ($transformation->getParametersWithKey('twig-extension') as $extension) {
            $extensionValue = $extension->value();
            if (!class_exists($extensionValue)) {
                throw new \InvalidArgumentException('Unknown twig extension: ' . $extensionValue);
            }

            // to support 'normal' Twig extensions we check the interface to determine what instantiation to do.
            $implementsInterface = in_array(
                'phpDocumentor\Plugin\Twig\ExtensionInterface',
                class_implements($extensionValue)
            );

            $twigEnvironment->addExtension(
                $implementsInterface ? new $extensionValue($project, $transformation) : new $extensionValue()
            );
        }
    }
}
