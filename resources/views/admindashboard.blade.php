<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    This is the admin panel
                </div>
            </div>

            <div>
                <div class="row justify-content-center">

                    <div class="col-md-3">
                        <div class="card-counter info">
                            <i class="fa fa-users"></i>
                            <span class="count-numbers"> {{ $userCount }} </span>
                            <span class="count-name">Users</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-counter info">
                            <i class="fa fa-server"></i>
                            <span class="count-numbers"> {{ $server_count }} </span>
                            <span class="count-name">Server</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
