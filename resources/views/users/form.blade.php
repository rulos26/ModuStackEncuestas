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
    <div class="input-group">
        <input type="password" name="password" id="password" class="form-control" {{ $edit ? '' : 'required' }} minlength="6">
        <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fas fa-eye"></i></button>
    </div>
</div>
<div class="form-group">
    <label for="password_confirmation">Confirmar contraseña</label>
    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" {{ $edit ? '' : 'required' }} minlength="6">
</div>
<div class="mb-3">
    <label for="role" class="form-label">Rol</label>
    <select name="role" id="role" class="form-control" required>
        <option value="">Seleccione un rol</option>
        @foreach(Spatie\Permission\Models\Role::all() as $role)
            <option value="{{ $role->name }}" {{ (isset($user) && $user->roles->first() && $user->roles->first()->name == $role->name) || old('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
        @endforeach
    </select>
    @error('role')<span class="text-danger">{{ $message }}</span>@enderror
</div>
@push('js')
<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    var pwd = document.getElementById('password');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        this.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        pwd.type = 'password';
        this.innerHTML = '<i class="fas fa-eye"></i>';
    }
});
</script>
@endpush
