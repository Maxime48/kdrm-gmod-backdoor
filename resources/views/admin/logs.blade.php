@php
    use \App\Http\Controllers\kermini\adminLogic as adminLogic;
    use \App\Http\Controllers\kermini\userLogic as userLogic;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Latest logs') }}
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
                <div class="overflow-hidden">
                    <div class="p-6">

                    <div class="container-fluid mt-100">


                        <div class="d-flex flex-wrap justify-content-between">
                            <!--<div class="col-12 col-md-3 p-0 mb-3"> <input type="text" class="form-control" placeholder="Search..."> </div>-->
                        </div>

                        <div class="table100 ver1 m-b-110">
                            <div class="table100-head">
                                <table>
                                    <thead>
                                    <tr class="row100 head">
                                        <th class="cell100 column1">Message</th>
                                        <th class="cell100 column2">Level</th>
                                        <th class="cell100 column3">Date</th>
                                        <th class="cell100 column4">User</th>
                                        <th class="cell100 column5">Server</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="table100-body js-pscroll">
                                <table>
                                    <tbody>
                                    @foreach($logs as $log)
                                        <tr class="row100 body">
                                            <td class="cell100 column1">
                                                {{ \Illuminate\Support\Str::limit($log->message, 650, $end='...') }}
                                            </td>
                                            <td class="cell100 column2">
                                                <div class="alert
                                                            @if($log->level == "notice")
                                                                alert-info
                                                            @elseif($log->level == "warning")
                                                                alert-warning
                                                            @elseif($log->level == "critical")
                                                                alert-danger
                                                                @elseif($log->level == "alert")
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
                                            </td>
                                            <td class="cell100 column3">
                                                {{ $log->created_at }}
                                                <br>
                                                {{ adminLogic::time_elapsed_string($log->created_at) }}
                                            </td>
                                            <td class="cell100 column4">
                                                @if( $log->user_id!= null)
                                                    <a style="text-decoration: none;" href="{{ route('showUserProfile', ['id' => $log->user_id]) }}" >
                                                        {{ adminLogic::getUserById($log->user_id)->name }}
                                                    </a>
                                                @else
                                                    No user related to this log
                                                @endif
                                            </td>
                                            <td class="cell100 column5">
                                                @if( $log->server != "none" )
                                                    ServerDetected-ImplementDisplay
                                                @else
                                                    No server related to this log
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="btn-group mx-auto mt-2"  role="group">
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
