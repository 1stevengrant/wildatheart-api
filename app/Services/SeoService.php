<?php

namespace App\Services;

use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;

class SeoService
{
    public function setMetadata(string $title, string $description, ?string $image, string $url): void
    {
        $this->setSEOMeta($title, $description, $url);
        $this->setOpenGraph($title, $description, $url, $image);
        $this->setTwitterCard($title, $description, $image);
        $this->setJsonLd($title, $description, $url, $image);
    }

    protected function setSEOMeta(string $title, string $description, string $url): void
    {
        SEOMeta::setTitle($title)
            ->setDescription($description)
            ->setCanonical($url);
    }

    protected function setOpenGraph(string $title, string $description, string $url, ?string $image): void
    {
        OpenGraph::setTitle($title)
            ->setUrl($url)
            ->setDescription($description)
            ->addProperty('type', 'articles')
            ->addImage($image)
            ->addProperty('locale', 'en-US');
    }

    protected function setTwitterCard(string $title, string $description, ?string $image): void
    {
        TwitterCard::setTitle($title)
            ->addImage($image)
            ->setDescription($description)
            ->setSite('@wildatheart');
    }

    protected function setJsonLd(string $title, string $description, string $url, ?string $image): void
    {
        JsonLd::setTitle($title)
            ->setDescription($description)
            ->addImage($image)
            ->setUrl($url);

        JsonLd::generate();
    }
}
