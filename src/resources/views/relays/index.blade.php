@extends('layouts.application', [
    'nav' => 'relays'
])
@section('content')
    <h1>Relays</h1>
    
    <table class="table table-striped table-hover">
        <thead class="stylish-color-dark text-white">
            <tr>
                <th>Relay</th>
                <th>Streaming</th>
                <th>Viewers</th>
                <th>Resolution</th>
                <th>Bitrate</th>
                <th>Drops</th>
            </tr>
        </thead>
        <tbody>
            @foreach($relays as $relay)
            <tr>
                <td>
                    <i class="fa fa-circle {{ $relay->active ? 'text-success' : 'text-danger' }}" aria-hidden="true"></i>
                    <a href="{{ route('relays.show', $relay) }}">{{ $relay->name }}</a>
                </td>
                <td>
                    @if ($relay->isLive())
                        <span class="badge badge-success">Live</span>
                    @else
                        <span class="badge badge-danger">Offline</span>
                    @endif
                </td>
                <td>{{ $relay->twitch_viewers }}</td>
                <td>
                    @if ($relay->isLive())
                        {{ $relay->width }}x{{ $relay->height }}{{ '@' }}{{ $relay->fps }}
                    @endif
                </td>
                <td>
                    @if ($relay->isLive())
                        {{ humansize($relay->video_bitrate, true) }}
                    @endif
                </td>
                <td> {{ $relay->source_drops }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection