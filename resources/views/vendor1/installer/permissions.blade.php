@extends('vendor.installer.layouts.master')

@section('title', trans('installer_messages.permissions.title'))
@section('container')
    @if (isset($permissions['errors']))
        <div class="alert alert-danger">Please fix the below error and the click  {{ trans('installer_messages.checkPermissionAgain') }}</div>
    @endif
    <ul class="list">
        @foreach($permissions['permissions'] as $permission)
        <li class="list__item list__item--permissions {{ $permission['isSet'] ? 'success' : 'error' }}">
            {{ strtolower($permission['folder']) }}<span>{{ $permission['permission'] }}</span>
        </li>
        @endforeach
    </ul>

	<p style="background: #f7f7f9;padding: 10px;font-size:14px">
        chmod -R 777 storage/app/ storage/framework/ storage/logs/ bootstrap/cache/
    </p>

    <div class="buttons">
        @if ( ! isset($permissions['errors']))
            <a class="button" href="{{ route('LaravelInstaller::database') }}">
                {{ trans('installer_messages.next') }}
            </a>
        @else
            <a class="button" href="javascript:window.location.href='';">
                {{ trans('installer_messages.checkPermissionAgain') }}
            </a>
        @endif
    </div>

@stop
