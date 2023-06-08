<?php

namespace App\Http\Livewire;

use App\Models\Issue;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class ReportIssue extends Modal
{
    public string $email = '';

    public string $name = '';

    public string $subject = '';

    public string $description = '';

    public function handle()
    {
        $this->validate([
            'email' => 'required|email|max:255',
            'name' => 'required|max:255',
            'subject' => 'required|max:255',
            'description' => 'required',
        ]);

        Issue::create([
            'email' => $this->email,
            'name' => $this->name,
            'subject' => $this->subject,
            'description' => $this->description,
        ]);

        $this->emit('issueReported');
        $this->dispatchBrowserEvent('notify', 'Issue reported successfully!');
        $this->close();
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.report-issue');
    }
}
