<?php

namespace Exploreo\Model;

use Exploreo\VillaMetaData;
use Exploreo\Language;
use WP_Post;

class Villa
{
    /**
     * @var WP_Post
     */
    private $post;

    /**
     * @var array
     */
    private $postMeta;

    /**
     * @var array $images
     */
    private $images;

    /**
     * @var array $distances
     */
    private $distances;

    public function __construct(WP_Post $post, array $postMeta)
    {
        $this->post = $post;
        $this->postMeta = $postMeta;
        $this->images = unserialize($this->postMeta[VillaMetaData::META_KEY_MEDIA_PHOTOS][0]);

    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function getImageUrl(int $width, int $height, array $image): string
    {
        return "https://media.villaforyou.net/photo/$width/$height/{$image['Object']}";
    }

    public function getDistances(): array
    {
        if (null === $this->distances) {
            $this->distances = unserialize($this->postMeta[VillaMetaData::META_KEY_DISTANCES][0]);
        }
        return $this->distances;
    }

    public function getHouseType($language = Language::EXPLOREO_LANGUAGE_KEY_ENGLISH): string
    {
        return unserialize($this->postMeta[VillaMetaData::META_KEY_HOUSE_TYPE][0])[$language];
    }

    public function getMetaByKey(string $key): string
    {
        return $this->postMeta[$key][0];
    }

}