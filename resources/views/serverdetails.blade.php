<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Server Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        @if (session('status') !== null)
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="alert alert-warning" role="alert">
                    {{ session('status') }}
                    @foreach($errors->all() as $error)
                        <div>
                            {{$error}}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @if ($status ?? null !== null)
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="alert alert-warning" role="alert">
                    {{ $status }}
                </div>
            </div>
        @endif
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">


                    <div class="container-fluid mt-100">
                        @if($Exception!=null)
                            <div class="card">
                                <div class="card-header">
                                    Saved server information for {{ $server->ip . ':' . $server->port }}
                                </div>
                                <div class="card-body">
                                    <blockquote class="blockquote mb-0">
                                        <p>{{$server->name}}</p>
                                        <footer class="blockquote-footer">Last known <cite title="Source Title">Server
                                                name</cite></footer>
                                    </blockquote>
                                    <blockquote class="blockquote mb-0">
                                        <p>{{$server->players}}</p>
                                        <footer class="blockquote-footer">Last known <cite title="Source Title">Player
                                                count</cite></footer>
                                    </blockquote>
                                    <blockquote class="blockquote mb-0">
                                        <p>{{$server->status}}</p>
                                        <footer class="blockquote-footer">Last known <cite
                                                title="Source Title">Status</cite></footer>
                                    </blockquote>
                                </div>
                            </div>
                        @else
                            <table class="table table-bordered table-striped">
                                <thead>
                                        <th colspan="2"
                                            class="info-column"
                                            style="text-align: center;">
                                            Server Info
                                        </th>
                                </thead>
                                <tbody>
                                @if( !empty( $Info ) )
                                    @foreach( $Info as $InfoKey => $InfoValue )
                                    <tr>
                                        <td>{{ $InfoKey }}</td>
                                        <td>
                                            @if( is_array( $InfoValue ) )
                                                <pre>
                                                    {{ $InfoValue }}
                                                </pre>
                                            @else
                                                @if( $InfoValue === true )
                                                    true
                                                @elseif( $InfoValue === false )
                                                    false
                                                @else
                                                    {{ htmlspecialchars($InfoValue) }}
                                                @endif

                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2">No information received</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                            <table class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th style="text-align: center;">Players:
                                        <span class="label label-info">
                                            {{ count( $Players ) }}
                                        </span> |
                                        <a class="btn btn-outline-primary" style="text-decoration: none;" href="{{route('scrgbMenu', ['serverid' => $server->id])}}" >
                                            💻ScreenGrabber💻
                                        </a>
                                    </th>
                                    <th class="frags-column">Frags</th>
                                    <th class="frags-column">Time</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if( !empty( $Players ) )
                                    @foreach( $Players as $Player )
                                        <tr>
                                            <td>{{ htmlspecialchars( $Player[ 'Name' ] ) }}</td>
                                            <td>{{ $Player[ 'Frags' ] }}</td>
                                            <td>{{ $Player[ 'TimeF' ] }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3">No players received</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                            <table class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th colspan="2" style="text-align: center;">
                                        Rules: <span class="label label-info">{{ count($Rules) }}</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if( !empty( $Rules ) )
                                    @foreach( $Rules as $Rule => $Value )
                                        <tr>
                                            <td>{{ htmlspecialchars( $Rule ) }}</td>
                                            <td>{{ htmlspecialchars( $Value ) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2">No rules received</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
