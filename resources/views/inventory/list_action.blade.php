<a href="{{ route('inventory.show', $id) }}" class="table-action">fortsetzen</a>
<a href="{{ route('inventory.finish', $id) }}" class="table-action" onclick="return confirm('Sicher?')">abschließen</a>