@component('mail::message')
# New Team Member Added

Hello {{ $team->owner->name }},

{{ $user->name }} ({{ $user->email }}) has been added to your team "{{ $team->name }}".

@component('mail::button', ['url' => route('teams.members')])
View Team Members
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
