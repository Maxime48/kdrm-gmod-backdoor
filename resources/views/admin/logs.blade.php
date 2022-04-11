@php
    use \App\Http\Controllers\kermini\adminLogic as adminLogic;
    use \App\Http\Controllers\kermini\userLogic as userLogic;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Latest logs') }}
            @foreach($errors->all() as $error)
                <div>
                    {{$error}}
                </div>
            @endforeach
        </h2>
    </x-slot>

    <div class="py-12">
        @if (session('status') !== null)
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="alert alert-warning" role="alert">
                    {{ session('status') }}
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
                                    <div class="col ml-2">Message</div>
                                    <div class="col-10 text-muted">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col-3">Level</div>
                                            <div class="col-3">Date</div>
                                            <div class="col-2">User</div>
                                            <div class="col-3">Server</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @foreach ($logs as $log)
                                <hr class="m-0">
                                <div class="card-body py-3">
                                    <div class="row no-gutters align-items-center">

                                        <div class="col-2">

                                            {{ \Illuminate\Support\Str::limit($log->message, 650, $end='...') }}

                                        </div>

                                        <div class="d-none d-md-block col-2">
                                            <div class="alert
                                                            @if($log->level == "notice")
                                                                alert-info
                                                            @elseif($log->level == "warning")
                                                                alert-warning
                                                            @elseif($log->level == "critical")
                                                                alert-danger
                                                            @elseif($log->level == "debug")
                                                                alert-primary
                                                            @elseif($log->level == "info")
                                                                alert-success
                                                            @elseif($log->level == "emergency")
                                                                alert-danger
                                                            @else
                                                                alert-light
                                                            @endif
                                                        "
                                                 role="alert">
                                                    <div style="text-align: center;">
                                                        {{ $log->level }}
                                                    </div>
                                            </div>
                                        </div>

                                        <div class="media col-3 align-items-center">
                                            <div class="media-body flex-truncate ml-2">
                                                <div class="line-height-1 text-truncate align-items-center">

                                                    {{ $log->created_at }}
                                                    <br>
                                                    {{ adminLogic::time_elapsed_string($log->created_at) }}

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-2">
                                            <a style="text-decoration: none;" href="{{ route('showUserProfile', ['id' => $log->user_id]) }}" >
                                                {{ adminLogic::getUserById($log->user_id)->name }}
                                            </a>
                                        </div>

                                        <div class="col-3">
                                            @if( $log->server != "none" )
                                                ServerDetected-ImplementDisplay
                                            @else
                                                No server related to this log
                                            @endif
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
                                        <button type="button" onclick="location.href='{{route('adminLogs', ['pageid' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                                    @endif
                                @else
                                    <button type="button" onclick="location.href='{{route('adminLogs', ['pageid' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                                @endif
                            @endfor
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
