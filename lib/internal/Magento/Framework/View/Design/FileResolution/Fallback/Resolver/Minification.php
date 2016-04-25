<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\FileResolution\Fallback\Resolver;

use Magento\Framework\View\Asset\Minification as AssetMinification;
use Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Resolver for minified asset, when minified is requested but not found
 */
class Minification implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    protected $fallback;

    /**
     * @var array
     */
    protected $extensions;

    /**
     * @var AssetMinification
     */
    private $minification;

    /**
     * @param ResolverInterface $fallback
     * @param AssetMinification $minification
     */
    public function __construct(ResolverInterface $fallback, AssetMinification $minification)
    {
        $this->fallback = $fallback;
        $this->minification = $minification;
    }

    /**
     * Get path of file after using fallback rules
     *
     * @param string $type
     * @param string $file
     * @param string|null $area
     * @param ThemeInterface|null $theme
     * @param string|null $locale
     * @param string|null $module
     * @return string|false
     */
    public function resolve($type, $file, $area = null, ThemeInterface $theme = null, $locale = null, $module = null)
    {
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
        $path = $this->fallback->resolve($type, $file, $area, $theme, $locale, $module);

        /**
         * Minified version as priority one
         */
        if ($path && $this->minification->isMinifiedFilename($path)) {
            return $path;
        }

        /**
         * If minification is disabled - return already found path
         */
        if (!$this->minification->isEnabled($fileExtension)) {
            return $path;
        }

        /**
         * Try to find minified version of file,
         * or return already found path
         */
        return $this->fallback->resolve(
            $type,
            $this->minification->addMinifiedSign($file),
            $area,
            $theme,
            $locale,
            $module
        ) ?: $path;
    }
}
