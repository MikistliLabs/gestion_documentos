@extends('layouts.app')
@section('css')
<link href="{{ asset('css/arbol.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="container">
    <h2 class="mb-4">📂 Sistema de Gestión de Documentos</h2>

    {{-- Mensajes de éxito --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    {{-- Mensajes de error --}}
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        {{-- Formulario Crear Carpeta --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Crear Carpeta</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('carpetas.store') }}">
                        @csrf
                        <div class="form-group mb-2">
                            <label>Empresa</label>
                            <select id="empresa" class="form-control">
                                <option value="">Seleccione</option>
                                @foreach(\App\Models\Empresa::all() as $empresa)
                                    <option value="{{ $empresa->id_empresa }}">{{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label>Dirección</label>
                            <select id="direccion" class="form-control"></select>
                        </div>

                        <div class="form-group mb-2">
                            <label>Área</label>
                            <select name="id_area" id="area" class="form-control"></select>
                        </div>

                        <div class="form-group mb-2">
                            <label>Carpeta Padre (opcional)</label>
                            <select name="id_padre" id="carpeta_padre" class="form-control">
                                <option value="">-- Ninguna --</option>
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            <label>Nombre de Carpeta</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Crear Carpeta</button>
                    </form>
                </div>
            </div>
        </div>
        {{-- Formulario Subir Documento --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Subir Documento</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('documentos.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-2">
                            <label>Tipo de Archivo</label>
                            <select name="id_tipo_documento" class="form-control">
                                @foreach(\App\Models\TipoArchivo::all() as $tipo)
                                    <option value="{{ $tipo->id_tipo }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label>Empresa</label>
                            <select id="empresa_doc" class="form-control">
                                <option value="">Seleccione</option>
                                @foreach(\App\Models\Empresa::all() as $empresa)
                                    <option value="{{ $empresa->id_empresa }}">{{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label>Dirección</label>
                            <select id="direccion_doc" class="form-control"></select>
                        </div>
                        <div class="form-group mb-2">
                            <label>Área</label>
                            <select name="id_area_doc" id="area_doc" class="form-control"></select>
                        </div>

                        <div class="form-group mb-2">
                            <label>Carpeta</label>
                            <select name="id_carpeta" id="carpeta_doc_arch" class="form-control"></select>
                        </div>

                        <div class="form-group mb-2">
                            <label>Nombre del Documento</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>

                        <div class="form-group mb-2">
                            <label>Archivo</label>
                            <input type="file" name="archivo" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success">Subir Documento</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><i class="fa-solid fa-search"></i> Buscar documentos</div>
        <div class="card-body">
            <form id="formBuscar">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre del documento">
                    </div>
                    <div class="col-md-3">
                        <select name="empresa" class="form-control">
                            <option value="">Empresa</option>
                            @foreach(\App\Models\Empresa::all() as $empresa)
                                <option value="{{ $empresa->id_empresa }}">{{ $empresa->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="direccion" class="form-control">
                            <option value="">Dirección</option>
                            @foreach(\App\Models\Direccion::all() as $direccion)
                                <option value="{{ $direccion->id_direccion }}">{{ $direccion->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="area" class="form-control">
                            <option value="">Área</option>
                            @foreach(\App\Models\Area::all() as $area)
                                <option value="{{ $area->id_area }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Buscar</button>
            </form>
        </div>
    </div>
    {{-- Árbol de Carpetas --}}
    <div class="card">
        <div class="card-header">Árbol de Carpetas</div>
        <div class="card-body">
            <div id="carpetas-tree"></div>
        </div>

    </div>
</div>
{{-- Modal Editar Carpeta --}}
<div class="modal fade" id="modalEditarCarpeta" tabindex="-1">
    <div class="modal-dialog">
        <form id="formEditarCarpeta" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Editar Carpeta</h5>
            </div>
            <div class="modal-body">
                {{-- Nombre --}}
                <div class="mb-2">
                    <label>Nombre de Carpeta</label>
                    <input type="text" name="nombre" id="nombreEditarCarpeta" class="form-control" required>
                </div>

                {{-- Empresa --}}
                <div class="mb-2">
                    <label>Empresa</label>
                    <select name="id_empresa" id="empresaEditarCarpeta" class="form-control" required>
                        <option value="">Seleccione</option>
                        @foreach(\App\Models\Empresa::all() as $empresa)
                            <option value="{{ $empresa->id_empresa }}">{{ $empresa->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Dirección --}}
                <div class="mb-2">
                    <label>Dirección</label>
                    <select name="id_direccion" id="direccion_edit" class="form-control" required></select>
                </div>
                {{-- Área --}}
                <div class="mb-2">
                    <label>Área</label>
                    <select name="id_area" id="areaEditarCarpeta" class="form-control" required></select>
                </div>
                <div class="mb-2">
                    <label>Carpeta Padre (opcional)</label>
                    <select name="carpeta_padre_edit" id="carpeta_padre_edit" class="form-control">
                        <option value="">-- Ninguna --</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning">Actualizar</button>
            </div>
        </form>
    </div>
</div>
{{-- Modal Editar Documento --}}
<div class="modal fade" id="modalEditarDocumento" tabindex="-1">
    <div class="modal-dialog">
        <form id="formEditarDocumento" method="POST" class="modal-content" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Editar Documento</h5></div>
            <div class="modal-body">
                <div class="mb-2">
                    <label>Tipo de Archivo</label>
                    <select name="id_tipo_documento" id="tipo_archivo_edit" class="form-control">
                        @foreach(\App\Models\TipoArchivo::all() as $tipo)
                            <option value="{{ $tipo->id_tipo }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label>Carpeta</label>
                    <select name="id_carpeta" id="carpeta_doc_edit" class="form-control">
                        @foreach(\App\Models\Carpeta::all() as $carpeta)
                            <option value="{{ $carpeta->id_carpeta }}">{{ $carpeta->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="nombreEditarDocumento" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label>Archivo (opcional)</label>
                    <input type="file" name="archivo" class="form-control">
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning">Actualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
<script src="//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
<script>
$(document).ready(function () {
    // Select dependientes carpetas
    $('#empresa').change(function () {
        let id = $(this).val();
        $('#direccion').empty();
        $('#area').empty();
        $('#carpeta_padre').empty().append('<option value="">-- Ninguna --</option>');
        if (id) {
            $.get('{{ url("api/direcciones/") }}/' + id, function (data) {
                $('#direccion').append('<option value="">Seleccione</option>');
                data.forEach(function (item) {
                    $('#direccion').append('<option value="' + item.id_direccion + '">' + item.nombre + '</option>');
                });
            });
        }
    });
    $('#direccion').change(function () {
        let id = $(this).val();
        $('#area').empty();
        if (id) {
            $.get('{{ url("api/areas/") }}/' + id, function (data) {
                $('#area').append('<option value="">Seleccione</option>');
                data.forEach(function (item) {
                    $('#area').append('<option value="' + item.id_area + '">' + item.nombre + '</option>');
                });
            });
        }
    });
    $('#area').change(function () {
        let id = $(this).val();
        $('#carpeta_padre').empty().append('<option value="">-- Ninguna --</option>');
        $('#carpeta_doc').empty();
        if (id) {
            $.get('{{ url("api/carpeta/") }}/' + id, function (data) {
                data.forEach(function (item) {
                    $('#carpeta_padre').append('<option value="' + item.id_carpeta + '">' + item.nombre + '</option>');
                    $('#carpeta_doc').append('<option value="' + item.id_carpeta + '">' + item.nombre + '</option>');
                });
            });
        }
    });
    $(document).on('click', '.btn-editar-carpeta', function () {
        // Limpiar selects
        $('#nombreEditarCarpeta').empty();
        $('#direccion_edit').empty().append('<option value="">Seleccione</option>');
        $('#areaEditarCarpeta').empty().append('<option value="">Seleccione</option>');
        $('#carpeta_padre_edit').empty().append('<option value="">-- Ninguna --</option>');

        $('#formEditarCarpeta').attr('action', '{{ url("carpetas") }}' + '/' + $(this).data('id'));
        $('#nombreEditarCarpeta').val($(this).data('name'));
        $('#empresaEditarCarpeta').val($(this).data('id_empresa'));
        getDireccion($(this).data('id_empresa'), $(this).data('id_direccion'));
        getAreas($(this).data('id_direccion'), $(this).data('id_area'));
        getCarpeta($(this).data('id_area'),$(this).data('id_padre'));
        $('#modalEditarCarpeta').modal('show');

    });
    function cargarTree(filtros = {}) {
        $('#carpetas-tree').jstree("destroy").empty().jstree({
            'core': {
                'data': {
                    'url': '{{ url("api/tree") }}',
                    'dataType': 'json',
                    'data': filtros
                }
            }
        });
    }
    // Select dependientes carpetas
    $('#empresa_doc').change(function () {
        let id = $(this).val();
        $('#direccion_doc').empty();
        $('#area_doc').empty();
        $('#carpeta_padre').empty().append('<option value="">-- Ninguna --</option>');
        if (id) {
            $.get('{{ url("api/direcciones/") }}/' + id, function (data) {
                $('#direccion_doc').append('<option value="">Seleccione</option>');
                data.forEach(function (item) {
                    $('#direccion_doc').append('<option value="' + item.id_direccion + '">' + item.nombre + '</option>');
                });
            });
        }
    });
    $('#direccion_doc').change(function () {
        let id = $(this).val();
        $('#area_doc').empty();
        if (id) {
            $.get('{{ url("api/areas/") }}/' + id, function (data) {
                $('#area_doc').append('<option value="">Seleccione</option>');
                data.forEach(function (item) {
                    $('#area_doc').append('<option value="' + item.id_area + '">' + item.nombre + '</option>');
                });
            });
        }
    });
    $('#area_doc').change(function () {
        let id = $(this).val();
        $('#carpeta_doc_arch').empty();
        if (id) {
            $.get('{{ url("api/carpeta/") }}/' + id, function (data) {
                data.forEach(function (item) {
                    $('#carpeta_doc_arch').append('<option value="' + item.id_carpeta + '">' + item.nombre + '</option>');
                });
            });
        }
    });
    // Carga inicial sin filtros
    cargarTree();
    // Al enviar el formulario
    $('#formBuscar').submit(function(e){
        e.preventDefault();
        let filtros = $(this).serialize();
        let params = {};
        $(this).serializeArray().forEach(function(item) {
            if (item.value) {
                params[item.name] = item.value;
            }
        });
        cargarTree(params);
    });
    // funciones de consulta edicion
    function getDireccion(id_empresa, id_old,tipo){
       let id = id_empresa
        $('#direccion_edit').empty();
        if (id) {
            $.get('{{ url("api/direcciones/") }}/' + id, function (data) {
                $('#direccion_edit').append('<option value="">Seleccione</option>');
                data.forEach(function (item) {
                    $('#direccion_edit').append('<option value="' + item.id_direccion + '"' + (item.id_direccion == id_old ? ' selected' : item.id_direccion) + '>' + item.nombre + '</option>');
                });
            });
        }
    }
    function getAreas(id_area, id_old){
       let id = id_area
        $('#id_area').empty();
        if (id) {
            $.get('{{ url("api/areas/") }}/' + id, function (data) {
                $('#areaEditarCarpeta').append('<option value="">Seleccione</option>');
                data.forEach(function (item) {
                    $('#areaEditarCarpeta').append('<option value="' + item.id_area + '"' + (item.id_area == id_old ? ' selected' : '') + '>' + item.nombre + '</option>');
                });
            });
        }
    }
    function getCarpeta(id_area, id_old){
        let id = id_area;
        $('#carpeta_padre').empty().append('<option value="">-- Ninguna --</option>');
        $('#carpeta_doc').empty();
        if (id) {
            $.get('{{ url("api/carpeta/") }}/' + id, function (data) {
                data.forEach(function (item) {
                    $('#carpeta_padre_edit').append('<option value="' + item.id_area + '"' + (item.id_area == id_old ? ' selected' : '') + '>' + item.nombre + '</option>');
                });
            });
        }
    }
    // Editar Documento
    $(document).on('click', '.btn-editar-documento', function () {
       let id      = $(this).data('id');
        let nombre  = $(this).data('nombre');
        let tipo    = $(this).data('id_tipo_documento');
        let carpeta = $(this).data('id_carpeta');

        $('#formEditarDocumento').attr('action', '{{ url("documentos/") }}/' + id);
        $('#nombreEditarDocumento').val(nombre);
        $('#tipo_archivo_edit').val(tipo);
        $('#carpeta_doc_edit').val(carpeta);
        $('#modalEditarDocumento').modal('show');
    });
    // Eliminamos carpeta o documento
        $(document).on('click', '.btn-delete', function () {
        let id = $(this).data('id');
        let tipo = $(this).data('type'); // "carpeta" o "documento"
        let nombre = $(this).data('name');

        // --- SOLUCIÓN: Validar que el ID exista antes de continuar ---
        if (!id || !tipo) {
            console.error('Intento de eliminación con ID o tipo inválido.');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo obtener la información para eliminar.'
            });
            return; // Detiene la ejecución si no hay ID
        }

        // Construir URL correcta con trernario
        let url = (tipo === 'carpeta')
            ? '{{ url("carpetas_baja") }}/' + id
            : '{{ url("documentos_baja") }}/' + id;

        Swal.fire({
            title: `¿Eliminar ${tipo}?`,
            text: `Se eliminará "${nombre}" y esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE' 
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: response.success || `${tipo} eliminado correctamente`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        cargarTree();
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al eliminar. Revisa la consola.'
                        });
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    });
});
</script>
@endsection
