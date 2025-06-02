<div>
    <ul style="padding-left: 1em;">
        @foreach($familiares as $f)
            <li>
                <strong>{{ $f->nombre }} {{ $f->apellidos }}</strong> ({{ $f->rut }})<br>
                Parentesco: <span>{{ $f->pivot->parentesco }}</span>
            </li>
        @endforeach
    </ul>
</div>
