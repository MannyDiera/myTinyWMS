@extends('article_group.form')

@section('title', __('Neue Artikelgruppe'))

@section('breadcrumb')
    <li>
        <a href="{{ route('article.index') }}">@lang('Artikel')</a>
    </li>
    <li>
        <a href="{{ route('article-group.index') }}">@lang('Artikelgruppen verwalten')</a>
    </li>
    <li class="active">
        <strong>@lang('Neue Artikelgruppe')</strong>
    </li>
@endsection

@section('form_start')
    {!! Form::model($articleGroup, ['route' => ['article-group.store'], 'method' => 'POST']) !!}
@endsection

@section('submit')
    {!! Form::submit(__('Speichern'), ['class' => 'btn btn-primary', 'id' => 'submit']) !!}
@endsection