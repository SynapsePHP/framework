<?php

namespace Synapse;

use Intervention\Image\ImageManager;

class Image
{
    private ImageManager $_manager;

    public function __construct()
    {
        $this->_manager = new ImageManager(['driver' => 'gd']);
    }

    /**
     *
     * Open an image, run orientate automatically on it and return the instance
     *
     * @param string $file
     * @return \Intervention\Image\Image
     *
     */
    public function open(string $file): \Intervention\Image\Image
    {
        return $this->_manager->make($file)->orientate();
    }

    /**
     *
     * Generate a nice thumbnail (100x100)
     *
     * @param string $image
     * @return \Intervention\Image\Image
     *
     */
    public function generateThumbnail(string $image): \Intervention\Image\Image
    {
        return $this->_manager->make($image)->orientate()->crop(100, 100);
    }

    /**
     *
     * Resize image by width and respect aspect ratio and prevent upsizing
     *
     * @param string $image
     * @param int $width
     * @return \Intervention\Image\Image
     *
     */
    public function resizeByWidth(string $image, int $width): \Intervention\Image\Image
    {
        return $this->_manager->make($image)->orientate()->resize($width, null, function($constraint)
        {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }

    /**
     *
     * Resize image by height and respect aspect ratio and prevent upsizing
     *
     * @param string $image
     * @param int $height
     * @return \Intervention\Image\Image
     *
     */
    public function resizeByHeight(string $image, int $height): \Intervention\Image\Image
    {
        return $this->_manager->make($image)->orientate()->resize(null, $height, function($constraint)
        {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }
}