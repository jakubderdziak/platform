<?php

namespace Oro\Bundle\LayoutBundle\Provider;

use Imagine\Image\ImageInterface;

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

use Oro\Bundle\LayoutBundle\Model\ThemeImageTypeDimension;

/**
 * Load dimensions from theme config to LiipImagine filters
 */
class ImageFilterProvider
{
    const IMAGE_QUALITY = 95;
    const BACKGROUND_COLOR = '#fff';
    const RESIZE_MODE = ImageInterface::THUMBNAIL_INSET;

    /**
     * @var ImageTypeProvider
     */
    protected $imageTypeProvider;

    /**
     * @var FilterConfiguration
     */
    protected $filterConfiguration;

    /**
     * @param ImageTypeProvider $imageTypeProvider
     * @param FilterConfiguration $filterConfiguration
     */
    public function __construct(ImageTypeProvider $imageTypeProvider, FilterConfiguration $filterConfiguration)
    {
        $this->imageTypeProvider = $imageTypeProvider;
        $this->filterConfiguration = $filterConfiguration;
    }

    public function load()
    {
        foreach ($this->getAllDimensions() as $dimension) {
            $filterName = $dimension->getName();
            $this->filterConfiguration->set(
                $filterName,
                $this->buildFilterFromDimension($dimension)
            );
        }
    }

    /**
     * @return ThemeImageTypeDimension[]
     */
    private function getAllDimensions()
    {
        $dimensions = [];

        foreach ($this->imageTypeProvider->getImageTypes() as $imageType) {
            $dimensions = array_merge($dimensions, $imageType->getDimensions());
        }

        return $dimensions;
    }

    /**
     * @param ThemeImageTypeDimension $dimension
     * @return array
     */
    private function buildFilterFromDimension(ThemeImageTypeDimension $dimension)
    {
        $width = $dimension->getWidth();
        $height = $dimension->getHeight();
        $withResize = $dimension->getWidth() && $dimension->getHeight();

        $filterSettings = [
            'quality' => self::IMAGE_QUALITY,
            'filters' => [
                'strip' => [],
//                'watermark' => [
//                    'image' => 'Resources/watermark.png',
//                    'size' => 0.8,
//                    'position' => 'center' //topleft,top,topright,left,center,right,bottomleft,bottom,bottomright
//                ]
            ]
        ];

        if ($withResize) {
            $filterSettings = array_merge_recursive(
                [
                    'filters' => [
                        'thumbnail' => [
                            'size' => [$width, $height],
                            'mode' => self::RESIZE_MODE,
                            'allow_upscale' => true
                        ],
                        'background' => [
                            'size' => [$width, $height],
                            'color' => self::BACKGROUND_COLOR
                        ]
                    ]
                ],
                $filterSettings
            );
        }

        return $filterSettings;
    }
}
