@php
    use \App\Http\Controllers\kermini\adminLogic as adminLogic;
    use \App\Http\Controllers\kermini\userLogic as userLogic;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __($type . ' Screen Grab') }}
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">


                    <div class="container-fluid mt-100">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th style="text-align: center;">Players:
                                    <span class="label label-info">
                                            {{ count( $Players ) }}
                                    </span>
                                </th>
                                <th style="text-align: center;">Launch</th>
                                <th class="frags-column">Frags</th>
                                <th class="frags-column">Time</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if( !empty( $Players ) )
                                @foreach( $Players as $Player )
                                    @if($Player[ 'Name' ] != "")
                                        <tr>
                                            <td>{{ htmlspecialchars( $Player[ 'Name' ] ) }}</td>
                                            <td style="text-align: center;">
                                                <form method="POST" action="{{ route('sendFastSCRGBPayload', ['serverid' => $serverid]) }}">
                                                    @csrf
                                                    <div>
                                                        <x-input id="player" class="block mt-1 w-full" type="text" name="player" value="{{$Player[ 'Name' ]}}" hidden readonly required  />
                                                    </div>

                                                    <div>

                                                        <x-button class="mt-1 btn btn-primary">
                                                            ☢ {{ __('Launch') }} ☢
                                                        </x-button>

                                                    </div>
                                                </form>
                                            </td>
                                            <td>{{ $Player[ 'Frags' ] }}</td>
                                            <td>{{ $Player[ 'TimeF' ] }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3">No players received</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
