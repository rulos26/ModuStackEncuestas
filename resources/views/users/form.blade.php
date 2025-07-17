<div class="form-group">
    <label for="name">Nombre</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required maxlength="255">
</div>
<div class="form-group">
    <label for="email">Correo electrónico</label>
    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required maxlength="255">
</div>
<div class="form-group">
    <label for="password">Contraseña {{ $edit ? '(dejar en blanco para no cambiar)' : '' }}</label>
    <input type="password" name="password" id="password" class="form-control" {{ $edit ? '' : 'required' }} minlength="6">
</div>
<div class="form-group">
    <label for="password_confirmation">Confirmar contraseña</label>
    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" {{ $edit ? '' : 'required' }} minlength="6">
</div>
<div class="mb-3">
    <label for="roles" class="form-label">Roles</label>
    <select name="roles[]" id="roles" class="form-control" multiple required>
        @foreach(Spatie\Permission\Models\Role::all() as $role)
            <option value="{{ $role->name }}" {{ (isset($user) && $user->hasRole($role->name)) || (is_array(old('roles')) && in_array($role->name, old('roles', []))) ? 'selected' : '' }}>{{ $role->name }}</option>
        @endforeach
    </select>
    @error('roles')<span class="text-danger">{{ $message }}</span>@enderror
</div>
