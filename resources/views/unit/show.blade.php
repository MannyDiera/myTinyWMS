@extends('unit.form')

@section('title', __('Einheit bearbeiten'))

@section('breadcrumb')
    <li>
        <a href="{{ route('admin.index') }}">@lang('Administrator')</a>
    </li>
    <li>
        <a href="{{ route('unit.index') }}">@lang('Einheit Übersicht')</a>
    </li>
    <li class="active">
        <strong>@lang('Einheit bearbeiten')</strong>
    </li>
@endsection

@section('form_start')
    {!! Form::model($unit, ['route' => ['unit.update', $unit], 'method' => 'PUT']) !!}
@endsection

@section('submit')
    {!! Form::submit(__('Speichern'), ['class' => 'btn btn-primary']) !!}
@endsection

@section('secondCol')
    <div class="w-1/3 ml-4">
        <collapse title="@lang('Logbuch')">
            @include('components.audit_list', $audits)
        </collapse>
    </div>
@endsection