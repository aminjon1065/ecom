<x-mail::message>
# {{ $subjectLine }}

{!! nl2br(e($bodyText)) !!}

С уважением,<br>
{{ config('app.name') }}
</x-mail::message>
