
<x-mailcoach::layout
    :originTitle="$originTitle ?? app(\Spatie\Mailcoach\Domain\Settings\SettingsNavigation::class)->current()['title'] ?? __mc('Settings')"
    :originHref="$originHref ?? app(\Spatie\Mailcoach\Domain\Settings\SettingsNavigation::class)->current()['url'] ?? ''"
    :title="$title ?? null"
    :hideCard="$hideCard ?? false"
    :create="$create ?? null"
    :create-text="$createText ?? null"
    :create-data="$createData ?? []"
    :create-component="$createComponent ?? null"
>
    <x-slot name="nav">
        <x-mailcoach::navigation>
            @foreach (app(\Spatie\Mailcoach\Domain\Settings\SettingsNavigation::class)->tree() as $item)
                
                @if(count($item['children']))
                    <x-mailcoach::navigation-group :title="__($item['title'])" :href="$item['url']">
                        @foreach($item['children'] as $child)
                            <x-mailcoach::navigation-item :href="$child['url']" :active="$child['active']">
                                {{ __($child['title']) }}
                            </x-mailcoach::navigation-item>
                            @if($child['title'] == "Mailers")
                            <x-mailcoach::navigation-item href="{{ url('/wasenders') }}" 
                            :active="request()->routeIs('wasenders.*') ? 'true' : ''">
                                {{ __mc('Wa Senders') }}
                            </x-mailcoach::navigation-item>
                            @endif
                        @endforeach
                    </x-mailcoach::navigation-group>
                @else
                    <x-mailcoach::navigation-group
                        :title="__mc($item['title'])"
                        :href="$item['url']"
                        :active="$item['active']"
                    />
                @endif
            @endforeach
        </x-mailcoach::navigation>
    </x-slot>

    {{ $slot }}
</x-mailcoach::layout>
