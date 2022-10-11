@php
    use \App\Http\Controllers\kermini\adminLogic as adminLogic;
    use \App\Http\Controllers\kermini\userLogic as userLogic;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All Blocked IPs') }}
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

                            <form method="POST" action="{{ route('AdminPostNew') }}">
                                @csrf
                                {!!  GoogleReCaptchaV3::renderField('AdminIpBlock', 'AdminIpBlock') !!}

                                <div class="mb-3 input-group">
                                    <input name="ip" type="text" class="form-control mr-2" placeholder="192.168.*.*">
                                    <x-button class="btn btn-primary">
                                        <i class="fas fa-ban"></i>
                                    </x-button>
                                </div>
                            </form>

                        </div>

                        <div class="table100 ver1 m-b-110">
                            <div class="table100-head">
                                <table>
                                    <thead>
                                    <tr class="row100 head">
                                        <th class="cell100 column1">IP</th>
                                        <th class="cell100 column2">Type</th>
                                        <th class="cell100 column3">Last Update</th>
                                        <th class="cell100 column3">Actions</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="table100-body js-pscroll">
                                <table>
                                    <tbody>
                                    @foreach($restrictions as $restriction)
                                        <tr class="row100 body">
                                            <td class="cell100 column1">
                                                {{ $restriction->forbiddenIp }}
                                            </td>
                                            <td class="cell100 column2">

                                                @if($restriction->global == 1)
                                                    <div class="alert alert-dark" role="alert">
                                                        Global
                                                    </div>
                                                @else
                                                    <div class="alert alert-primary" role="alert">
                                                        User-restricted by
                                                        <a style="text-decoration: none;" href="{{ route('showUserProfile', ['id' => $restriction->user_id]) }}" >
                                                            {{ adminLogic::getUserById($restriction->user_id)->name }}
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="cell100 column3">
                                                {{ $restriction->updated_at }}
                                                <br>
                                                {{ adminLogic::time_elapsed_string($restriction->updated_at) }}
                                            </td>
                                            <td class="cell100 column3">
                                                <a href="{{ route('AdminEditRestriction', ['restriction' => $restriction->id]) }}"  class="btn btn-secondary">Edit</a>
                                                <a href="{{ route('AdminDeleteRestriction', ['restriction' => $restriction->id]) }}"  class="btn btn-danger">
                                                    Delete
                                                </a>
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
                                        <button type="button" onclick="location.href='{{route('AdminBlockedIps', ['pageid' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                                    @endif
                                @else
                                    <button type="button" onclick="location.href='{{route('AdminBlockedIps', ['pageid' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                                @endif
                            @endfor
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
