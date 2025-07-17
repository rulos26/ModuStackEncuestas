@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Documentación: Módulos de Usuarios y Roles</h1>
    <hr>
    <h2>Gestión de Usuarios</h2>
    <ul>
        <li><b>Listado de usuarios:</b> Tabla interactiva con búsqueda, orden y paginación (DataTables). Exportación a CSV/Excel.</li>
        <li><b>Crear usuario:</b> Formulario con campos: nombre, correo, contraseña (con botón ver/ocultar) y selección de un solo rol.</li>
        <li><b>Editar usuario:</b> Permite modificar nombre, correo, contraseña y rol asignado.</li>
        <li><b>Ver usuario:</b> Muestra detalles del usuario (sin ID ni botón de editar).</li>
        <li><b>Eliminar usuario:</b> Elimina el usuario de forma permanente.</li>
        <li><b>Validación:</b> Todos los campos son validados. El rol es obligatorio y debe existir en la base de datos.</li>
        <li><b>Seguridad:</b> Contraseñas encriptadas, protección CSRF y validación robusta.</li>
    </ul>
    <h2>Gestión de Roles</h2>
    <ul>
        <li><b>Listado de roles:</b> Visualiza todos los roles existentes y sus permisos asociados.</li>
        <li><b>Crear rol:</b> Permite definir un nombre único y asignar permisos.</li>
        <li><b>Editar rol:</b> Modifica nombre y permisos de un rol existente.</li>
        <li><b>Eliminar rol:</b> Elimina el rol si no está asignado a usuarios críticos.</li>
        <li><b>Asignación de roles:</b> Cada usuario puede tener un solo rol, asignado desde el formulario de usuario.</li>
        <li><b>Permisos:</b> Los roles gestionan el acceso a funcionalidades clave del sistema.</li>
    </ul>
    <h2>Buenas Prácticas</h2>
    <ul>
        <li>Utiliza roles predefinidos para mantener la seguridad y organización.</li>
        <li>Asigna solo los permisos necesarios a cada rol.</li>
        <li>Revisa periódicamente los usuarios y roles para evitar accesos indebidos.</li>
        <li>Utiliza la exportación de usuarios para auditorías y reportes.</li>
    </ul>
    <h2>Extensión y Personalización</h2>
    <ul>
        <li>El sistema permite agregar nuevos roles y permisos según las necesidades del negocio.</li>
        <li>La arquitectura modular facilita la integración de nuevos módulos de seguridad.</li>
    </ul>
</div>
@endsection
