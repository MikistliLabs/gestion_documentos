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
        <div class="card-header">🔍 <i class="fa-solid fa-pen"></i> Buscar documentos</div>
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
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Editar Carpeta</h5></div>
            <div class="modal-body">
                <input type="text" name="nombre" id="nombreEditarCarpeta" class="form-control" required>
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
                <input type="text" name="nombre" id="nombreEditarDocumento" class="form-control" required>
                <input type="file" name="archivo" class="form-control mt-2">
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
            $.get('{{ url("api/carpetas/") }}/' + id, function (data) {
                data.forEach(function (item) {
                    $('#carpeta_padre').append('<option value="' + item.id_carpeta + '">' + item.nombre + '</option>');
                    $('#carpeta_doc').append('<option value="' + item.id_carpeta + '">' + item.nombre + '</option>');
                });
            });
        }
    });
    $('#carpetas-tree').on('click', '.btn-edit', function (e) {
        e.stopPropagation();
        let id = $(this).data('id');
        let type = $(this).data('type');
        abrirModalEdicion(type, id);
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
            $.get('{{ url("api/carpetas/") }}/' + id, function (data) {
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
    // Editar Carpeta
    $(document).on('click', '.btn-editar-carpeta', function (e) {
        e.stopPropagation(); // evita que se abra/cierre el nodo
        let id = $(this).data('id');
        let nombre = $(this).data('nombre');
        $('#nombreEditarCarpeta').val(nombre);
        $('#formEditarCarpeta').attr('action', '{{ url("carpetas") }}/' + id);
        $('#modalEditarCarpeta').modal('show');
    });

    // Editar Documento
    $(document).on('click', '.btn-editar-documento', function () {
        let id = $(this).data('id');
        let nombre = $(this).data('nombre');
        $('#nombreEditarDocumento').val(nombre);
        $('#formEditarDocumento').attr('action', '{{ url("documentos") }}/' + id);
    });
});
function abrirModalEdicion(type, id) {
    fetch(`/${type}s/${id}/edit`)
        .then(res => res.json())
        .then(data => {
            if (type === 'carpeta') {
                let form = document.getElementById('formEditarCarpeta');
                form.action = `/carpetas/${id}`;
                document.getElementById('nombre_carpeta_edit').value = data.nombre;
                // Aquí llenas los select de empresa, dirección, área igual que antes
                $('#modalEditarCarpeta').modal('show');
            } else {
                let form = document.getElementById('formEditarDocumento');
                form.action = `/documentos/${id}`;
                document.getElementById('nombre_documento_edit').value = data.nombre;
                document.getElementById('tipo_archivo_edit').value = data.id_tipo_documento;
                $('#modalEditarDocumento').modal('show');
            }
        });
}
</script>
@endsection
