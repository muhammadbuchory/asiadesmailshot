<?php

namespace Spatie\LivewireWizard;

use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LivewireWizard\Support\EventEmitter;
use Spatie\LivewireWizard\Support\StepSynth;

class WizardServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-livewire-wizard')
            ->hasViews();
    }

    public function bootingPackage()
    {
        Livewire::propertySynthesizer(StepSynth::class);
        $this->registerLivewireTestMacros();
    }

    public function registerLivewireTestMacros()
    {
        Testable::macro('emitEvents', function () {
            return new EventEmitter($this);
        });

        Testable::macro('getStepState', function (?string $step = null) {
            return $this->instance()->getCurrentStepState($step);
        });
    }
}
