@php
    use \App\Http\Controllers\kermini\adminLogic as adminLogic;
    use \App\Http\Controllers\kermini\userLogic as userLogic;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
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


                        <div class="d-flex flex-wrap justify-content-between">
                            <!--<div class="col-12 col-md-3 p-0 mb-3"> <input type="text" class="form-control" placeholder="Search..."> </div>-->
                        </div>

                        <div class="card mb-3">
                            <div class="card-header pl-0 pr-0">
                                <div class="row no-gutters w-110 align-items-center">
                                    <div class="col-12 text-muted">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col-2">Name</div>
                                            <div class="col-1">Players</div>
                                            <div class="col-2">Ip</div>
                                            <div class="col-1">Port</div>
                                            <div class="col-2">Status</div>
                                            <div class="col-3">LastUpdate</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @foreach ($servers as $server)

                                <div class="card-body">
                                    <div class="row no-gutters">

                                        <div class="col-2">

                                            <a style="text-decoration: none;" href="{{ route('ServerDetails', ['serverid' => $server->id]) }}" >
                                                {{ $server->name }}
                                            </a>
                                        </div>

                                        <div class="col-1">

                                            {{ $server->players }}

                                        </div>

                                        <div class="col-2">

                                            {{ $server->ip }}

                                        </div>

                                        <div class="col-1">

                                            {{ $server->port }}

                                        </div>

                                        <div class="col-2">

                                            {{ $server->status }}

                                        </div>

                                        <div class="col-3">

                                            {{ adminLogic::time_elapsed_string($server->updated_at) }}
                                            |
                                            {{ $server->updated_at }}

                                        </div>

                                    </div>
                                </div>
                        @endforeach

                        <!--
                        <nav>
                            <ul class="pagination mb-5">
                                <li class="page-item disabled"><a class="page-link" href="javascript:void(0)" data-abc="true">«</a></li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)" data-abc="true">1</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)" data-abc="true">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)" data-abc="true">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)" data-abc="true">»</a></li>
                            </ul>
                        </nav>
                            -->
                        </div>
                        <div class="btn-group mx-auto"  role="group">
                            @for($i = 1; $i <= $buttons; $i++)
                                @if($buttons > 30)
                                    @if($i<=8 or ($pageid+5 >= $i and $pageid-5 <= $i) or $i >= ($buttons-8))
                                        <button type="button" onclick="location.href='{{route('dashboard', ['pageid' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                                    @endif
                                @else
                                    <button type="button" onclick="location.href='{{route('dashboard', ['pageid' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                                @endif
                            @endfor
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
