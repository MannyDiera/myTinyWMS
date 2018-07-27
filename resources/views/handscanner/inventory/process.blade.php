@extends('layout.handscanner')

@section('subheader')
    Inventur - Eingabe
@endsection

@section('back', route('handscanner.inventory.select_article', [$inventory, $article->category]))

@section('content')
    @if (!$article->category->is($category))
        <div class="alert alert-secondary">Achtung, Artikel ist aus anderer Kategorie!</div>
    @endif

    @if(!is_null($item->processed_at))
        <div class="alert alert-warning">Achtung, Artikel wurde bereits bearbeitet!</div>
    @endif

    <form method="post" action="{{ route('handscanner.inventory.processed', [$inventory, $article]) }}" id="saveinventory">
        @csrf

        <div class="row">
            <div class="col">
                <div class="row text-left">
                    <div class="col">
                        <div class="label">Name:</div>
                        <h6>{{ $article->name }}</h6>
                    </div>
                </div>

                <div class="row text-left">
                    <div class="col">
                        <div class="label">Nummer:</div>
                        <h5>{{ $article->article_number }}</h5>
                    </div>

                    <div class="col">
                        <div class="label">aktueller Bestand:</div>
                        <h5>{{ $article->quantity }}</h5>
                    </div>
                </div>

                <div class="row text-left">
                    <div class="col">
                        <div class="form-group">
                            <label for="quantity">neuer Bestand:</label>
                            <input type="number" min="0" inputmode="numeric" pattern="[0-9]*" name="quantity" id="quantity" required class="form-control form-control-lg">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-lg btn-success" id="changelogSubmit">Speichern</button>
                <a href="{{ route('handscanner.inventory.select_article', [$inventory, $article->category]) }}" class="btn btn-lg btn-secondary pull-right">Abbrechen</a>
            </div>

        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#saveinventory').submit(function () {
                var check = window.confirm('Bestand auf ' + $('#quantity').val() + ' ändern?');
                console.log(check);
                return check;
            });
        });
    </script>
@endpush