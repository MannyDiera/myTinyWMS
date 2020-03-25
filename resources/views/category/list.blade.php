@extends('layout.app')

@section('title', __('Kategorien'))

@section('breadcrumb')
    <li>
        <a href="{{ route('admin.index') }}">@lang('Administrator')</a>
    </li>
    <li class="active">
        <strong>@lang('Kategorien Übersicht')</strong>
    </li>
@endsection

@section('content')
    <div class="table-toolbar-right-content hidden">
        <a href="{{ route('category.create') }}" class="btn btn-primary">@lang('Neue Kategorie')</a>
    </div>

    {!! Form::open(['route' => ['category.print_list'], 'method' => 'POST']) !!}
    {!! $dataTable->table() !!}
    {!! Form::close() !!}

    <div class="footer_actions hidden">
        <button class="btn btn-xs btn-primary" type="submit">@lang('Lagerliste drucken')</button>
    </div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
@endpush