<a href="{{ route('category.show', $id) }}" class="btn btn-primary btn-xs">Details</a>
<form action="{{ route('category.destroy', $id) }}" class="list-form" method="POST">
    {{ method_field('DELETE') }}
    {{ csrf_field() }}

    <button class="btn btn-danger btn-xs" onclick="return confirm('Wirklich löschen?')">Löschen</button>
</form>