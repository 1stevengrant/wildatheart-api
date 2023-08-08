<?php

namespace App\Http\Livewire;

use App\Actions\HandleAttendee;
use App\Models\Campfire;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\TwitterCard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ViewCampfire extends Component
{
    public Campfire $campfire;

    public string $currentUrl = '';

    public bool $hideFooter = true;

    public bool $hasConnected = false;

    protected $listeners = [
        'registeredForCampfire' => 'updateConnectionStatus',
        'campfireUpdated' => '$refresh',
    ];

    public function updateConnectionStatus(): void
    {
        $this->hasConnected = true;
    }

    public function mount(Campfire $campfire): void
    {
        $this->campfire = $campfire;
        $this->currentUrl = url()->current();
        $title = $campfire->title.' by '.$campfire->host->full_name;
        $url = $this->currentUrl;
        $image = url('img/opengraph.jpeg');

        SEOMeta::setTitle($title)
            ->setCanonical($url);

        OpenGraph::setTitle($title)
            ->setUrl($url)
            ->addProperty('type', 'articles')
            ->addImage($image)
            ->addProperty('locale', 'en-US');

        TwitterCard::setTitle($title)
            ->addImage($image)
            ->setSite('@wildatheart');

        JsonLd::setTitle($title)
            ->setType('Event')
            ->addImage($image)
            ->setUrl($url);

        JsonLd::generate();
    }

    public function handleAttendee($campfireId): void
    {
        HandleAttendee::make()->handle(
            campfireId: $campfireId,
            currentUrl: $this->currentUrl
        );
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.view-campfire');
    }
}
