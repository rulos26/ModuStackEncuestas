<form action="{{ $route }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif
    <div class="mb-3">
        <label for="name" class="form-label">Nombre del Rol</label>
        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $role->name ?? '') }}" required>
        @error('name')<span class="text-danger">{{ $message }}</span>@enderror
    </div>
    <div class="mb-3">
        <label class="form-label">Permisos</label>
        <div class="row">
            @foreach($permissions as $perm)
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->name }}" id="perm_{{ $perm->id }}" {{ in_array($perm->name, old('permissions', $rolePermissions ?? [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_{{ $perm->id }}">{{ $perm->name }}</label>
                    </div>
                </div>
            @endforeach
        </div>
        @error('permissions')<span class="text-danger">{{ $message }}</span>@enderror
    </div>
    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
