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
            <div class="overflow-hidden">
                <div class="p-6">

                    <div class="container-fluid mt-100">


                        <div class="d-flex flex-wrap justify-content-between">
                            <div class="col-12 col-md-3 p-0 mb-3">
                                <input type="text" class="form-control" placeholder="Search...">
                            </div>
                            <a href="{{ route('UserBlockedIps') }}"  class="btn btn-secondary mb-2">
                                <i class="fas fa-shield-alt"></i>
                                IP Blocker
                            </a>
                        </div>

                        <div class="table100 ver1 m-b-110">
                            <div class="table100-head">
                                <table>
                                    <thead>
                                    <tr class="row100 head">
                                        <th class="cell100 column1">Name</th>
                                        <th class="cell100 column2">Players</th>
                                        <th class="cell100 column3">Ip</th>
                                        <th class="cell100 column4">Status</th>
                                        <th class="cell100 column5">Last Update</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="table100-body js-pscroll">
                                <table>
                                    <tbody>
                                        @foreach($servers as $server)
                                            <tr class="row100 body">
                                                <td class="cell100 column1">
                                                    <a href="{{ route('ServerDeletion', ['serverid' => $server->id]) }}"  class="btn btn-danger">
                                                        X
                                                    </a>
                                                    <a style="text-decoration: none;" href="{{ route('ServerDetails', ['serverid' => $server->id]) }}" >
                                                        {{ $server->name }}
                                                    </a>
                                                </td>
                                                <td class="cell100 column2">{{ $server->players }}</td>
                                                <td class="cell100 column3">
                                                    <a href="steam://connect/{{ $server->ip }}:{{ $server->port }}">
                                                        {{ $server->ip }}:{{ $server->port }}
                                                    </a>
                                                </td>
                                                <td class="cell100 column4">{{ $server->status }}</td>
                                                <td class="cell100 column5">{{ $server->updated_at }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

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
                        <div style="text-align: center;">
                            <div class="btn-group mx-auto pt-3"  role="group">
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
    </div>
</x-app-layout>
