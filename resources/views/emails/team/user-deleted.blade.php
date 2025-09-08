@component('mail::message')
# Team Member Removed

Hello {{ $team->owner->name }},

{{ $user->name }} ({{ $user->email }}) has been removed from your team "{{ $team->name }}".

@component('mail::button', ['url' => route('teams.members')])
View Team Members
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
