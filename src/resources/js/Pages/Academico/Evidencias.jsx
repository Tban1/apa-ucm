import { Head, useForm, router, usePage } from '@inertiajs/react';
import { useRef } from 'react';
import AppLayout from '@/Layouts/AppLayout';

export default function Evidencias({ periodo, nomina, plazo, puedeCargar, puedeCargarApelacion, apelacion, categorias, evidenciasPorCategoria, evidenciasApelacionPorCategoria }) {
    const { flash } = usePage().props;

    return (
        <>
            <Head title="Mis Evidencias" />
            <AppLayout title="Carga de Evidencias">

                {flash?.success && (
                    <div className="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
                        {flash.error}
                    </div>
                )}

                <EstadoBanner periodo={periodo} nomina={nomina} plazo={plazo} puedeCargar={puedeCargar} />

                {!periodo && (
                    <div className="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center text-yellow-800 text-sm">
                        No hay un período activo en este momento.
                    </div>
                )}

                {periodo && !nomina && (
                    <div className="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center text-yellow-800 text-sm">
                        No está en la nómina del período activo. Contacte a su secretario de facultad.
                    </div>
                )}

                {nomina?.observacion_secretario && (
                    <div className="mb-5 bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <p className="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-1">
                            Observaciones del secretario
                        </p>
                        <p className="text-sm text-amber-800">{nomina.observacion_secretario}</p>
                    </div>
                )}

                {nomina && (
                    <div className="space-y-4">
                        {categorias.map(cat => (
                            <CategoriaCard
                                key={cat.id}
                                categoria={cat}
                                evidencias={evidenciasPorCategoria[cat.id] ?? []}
                                puedeCargar={puedeCargar}
                                tieneObservaciones={!!nomina.observacion_secretario}
                            />
                        ))}
                    </div>
                )}

                {/* Sección evidencias de apelación */}
                {puedeCargarApelacion && (
                    <div className="mt-8">
                        <div className="flex items-center gap-3 mb-4">
                            <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">Evidencias de Apelación</h2>
                            <span className="text-xs font-medium px-2.5 py-0.5 rounded-full bg-orange-100 text-orange-700">Apelación aprobada</span>
                        </div>
                        {apelacion?.resolucion && (
                            <div className="mb-4 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-800">
                                <span className="font-semibold">Observación del secretario:</span> {apelacion.resolucion}
                            </div>
                        )}
                        <div className="space-y-4">
                            {categorias.map(cat => (
                                <CategoriaCard
                                    key={cat.id}
                                    categoria={cat}
                                    evidencias={evidenciasApelacionPorCategoria?.[cat.id] ?? []}
                                    puedeCargar={true}
                                    esApelacion={true}
                                    tieneObservaciones={false}
                                />
                            ))}
                        </div>
                    </div>
                )}

            </AppLayout>
        </>
    );
}

function EstadoBanner({ periodo, nomina, plazo, puedeCargar }) {
    if (!periodo) return null;

    const estadoLabels = {
        pendiente:      { label: 'Pendiente',       color: 'text-yellow-700 bg-yellow-100' },
        en_carga:       { label: 'En carga',         color: 'text-blue-700 bg-blue-100' },
        en_evaluacion:  { label: 'En evaluación',    color: 'text-purple-700 bg-purple-100' },
        evaluado:       { label: 'Evaluado',         color: 'text-green-700 bg-green-100' },
        cerrado:        { label: 'Cerrado',          color: 'text-gray-700 bg-gray-100' },
    };

    const estadoInfo = estadoLabels[nomina?.estado] ?? { label: nomina?.estado, color: 'text-gray-700 bg-gray-100' };

    const formatDate = (dateStr) => {
        if (!dateStr) return null;
        const [y, m, d] = dateStr.split('-');
        return `${d}/${m}/${y}`;
    };

    const plazoLicenciaVigente = nomina?.plazo_licencia
        ? new Date(nomina.plazo_licencia) >= new Date(new Date().toDateString())
        : false;

    return (
        <>
            {/* Banner de licencia médica */}
            {nomina?.con_licencia && (
                <div className="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4">
                    <div className="flex items-start gap-3">
                        <div className="flex-1">
                            <p className="text-sm font-semibold text-amber-800">Estás marcado/a con licencia médica</p>
                            {nomina.observacion_licencia && (
                                <p className="text-xs text-amber-700 mt-0.5">{nomina.observacion_licencia}</p>
                            )}
                            <div className="mt-2">
                                {nomina.plazo_licencia ? (
                                    <p className={`text-sm font-medium ${plazoLicenciaVigente ? 'text-green-700' : 'text-red-700'}`}>
                                        Plazo especial: <span className="font-bold">{formatDate(nomina.plazo_licencia)}</span>
                                        <span className="ml-1.5 text-xs font-normal">
                                            ({plazoLicenciaVigente ? 'vigente' : 'vencido'})
                                        </span>
                                    </p>
                                ) : (
                                    <p className="text-sm text-amber-700">
                                        El secretario de su facultad debe asignarle un plazo especial de entrega.
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}

            <div className="mb-6 bg-white border border-gray-200 rounded-xl p-4 flex flex-wrap items-center gap-x-6 gap-y-3">
                <div>
                    <p className="text-xs text-gray-400 uppercase tracking-wide font-medium">Período</p>
                    <p className="font-semibold text-gray-800 text-sm">{periodo.nombre}</p>
                </div>

                {!nomina?.con_licencia && plazo && (
                    <div className="border-l border-gray-200 pl-6">
                        <p className="text-xs text-gray-400 uppercase tracking-wide font-medium">Plazo de carga</p>
                        {plazo.cerrado ? (
                            <p className="font-semibold text-sm text-red-700">
                                Recepción cerrada
                                <span className="ml-1.5 font-normal text-xs opacity-80">({plazo.cerrado_en})</span>
                            </p>
                        ) : (
                            <p className={`font-semibold text-sm ${plazo.vigente ? 'text-green-700' : 'text-red-700'}`}>
                                {plazo.fecha_limite}
                                <span className="ml-1.5 font-normal text-xs opacity-80">
                                    ({plazo.vigente ? 'vigente' : 'vencido'})
                                </span>
                            </p>
                        )}
                    </div>
                )}

                {nomina && (
                    <div className="border-l border-gray-200 pl-6">
                        <p className="text-xs text-gray-400 uppercase tracking-wide font-medium">Estado expediente</p>
                        <span className={`inline-block mt-0.5 px-2 py-0.5 rounded-full text-xs font-semibold ${estadoInfo.color}`}>
                            {estadoInfo.label}
                        </span>
                    </div>
                )}

                <div className="ml-auto">
                    <span className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${
                        puedeCargar ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    }`}>
                        {puedeCargar ? 'Carga habilitada' : 'Carga no disponible'}
                    </span>
                </div>
            </div>
        </>
    );
}

function CategoriaCard({ categoria, evidencias, puedeCargar, tieneObservaciones, esApelacion = false }) {
    const fileRef = useRef(null);
    const { data, setData, post, processing, errors, reset } = useForm({
        categoria_id: categoria.id,
        archivo:      null,
        descripcion:  '',
    });

    const rutaStore   = esApelacion ? '/academico/evidencias-apelacion' : '/academico/evidencias';
    const rutaDelete  = esApelacion
        ? (id) => `/academico/evidencias-apelacion/${id}`
        : (id) => `/academico/evidencias/${id}`;

    function submit(e) {
        e.preventDefault();
        post(rutaStore, {
            forceFormData: true,
            onSuccess: () => {
                reset('archivo', 'descripcion');
                if (fileRef.current) fileRef.current.value = '';
            },
        });
    }

    function eliminar(id) {
        if (!confirm('¿Está seguro de eliminar esta evidencia?')) return;
        router.delete(rutaDelete(id));
    }

    return (
        <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
            {/* Cabecera */}
            <div className="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                <div>
                    <h3 className="font-semibold text-gray-800 text-sm">{categoria.nombre}</h3>
                    {categoria.descripcion && (
                        <p className="text-xs text-gray-500 mt-0.5">{categoria.descripcion}</p>
                    )}
                </div>
                <div className="flex items-center gap-2">
                    {evidencias.length === 0 ? (
                        <span className="text-xs font-medium bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">
                            Pendiente
                        </span>
                    ) : tieneObservaciones ? (
                        <span className="text-xs font-medium bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full">
                            Observada
                        </span>
                    ) : (
                        <span className="text-xs font-medium bg-green-100 text-green-700 px-2.5 py-1 rounded-full">
                            Entregada
                        </span>
                    )}
                    <span className="text-xs text-gray-400 bg-white border border-gray-200 px-2 py-0.5 rounded-full">
                        {evidencias.length} {evidencias.length === 1 ? 'archivo' : 'archivos'}
                    </span>
                </div>
            </div>

            <div className="px-5 py-4">
                {/* Lista de archivos subidos */}
                {evidencias.length > 0 && (
                    <ul className="mb-4 space-y-2">
                        {evidencias.map(ev => (
                            <li key={ev.id}
                                className="flex items-center justify-between py-2.5 px-3 bg-gray-50 rounded-lg border border-gray-100 text-sm"
                            >
                                <div className="flex items-center gap-2.5 min-w-0">
                                    <FileIcon />
                                    <div className="min-w-0">
                                        <p className="text-gray-800 font-medium truncate">{ev.nombre_archivo}</p>
                                        <p className="text-gray-400 text-xs mt-0.5">
                                            {ev.tamano} · {ev.created_at}
                                            {ev.descripcion && ` · ${ev.descripcion}`}
                                        </p>
                                    </div>
                                </div>
                                <div className="ml-3 flex items-center gap-2 shrink-0">
                                    <a
                                        href={ev.url_descarga}
                                        className="text-[#0096D6] hover:text-[#007ab5] transition-colors"
                                        title="Descargar archivo"
                                        download
                                    >
                                        <DownloadIcon />
                                    </a>
                                    {puedeCargar && (
                                        <button
                                            onClick={() => eliminar(ev.id)}
                                            className="text-red-400 hover:text-red-600 transition-colors"
                                            title="Eliminar evidencia"
                                        >
                                            <TrashIcon />
                                        </button>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                )}

                {/* Formulario de carga */}
                {puedeCargar ? (
                    <form onSubmit={submit}>
                        <div className="flex items-start gap-3">
                            <div className="flex-1 min-w-0">
                                <div
                                    onClick={() => fileRef.current?.click()}
                                    className="border border-dashed border-gray-300 rounded-lg px-4 py-3 text-sm text-gray-500 hover:border-[#1B2D6B] hover:text-[#1B2D6B] cursor-pointer transition-colors"
                                >
                                    {data.archivo
                                        ? <span className="text-gray-800 font-medium">{data.archivo.name}</span>
                                        : 'Seleccionar archivo — PDF, Word, JPG o PNG (máx. 10 MB)'}
                                </div>
                                <input
                                    ref={fileRef}
                                    type="file"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                    className="sr-only"
                                    onChange={e => setData('archivo', e.target.files[0] ?? null)}
                                />
                                {errors.archivo && (
                                    <p className="text-red-600 text-xs mt-1">{errors.archivo}</p>
                                )}
                            </div>
                            <button
                                type="submit"
                                disabled={!data.archivo || processing}
                                className="px-4 py-3 bg-[#1B2D6B] text-white text-sm font-medium rounded-lg hover:bg-[#152558] disabled:opacity-40 disabled:cursor-not-allowed transition-colors shrink-0"
                            >
                                {processing ? 'Subiendo…' : 'Subir'}
                            </button>
                        </div>
                    </form>
                ) : (
                    evidencias.length === 0 && (
                        <p className="text-xs text-gray-400 italic">Sin archivos cargados en esta categoría.</p>
                    )
                )}
            </div>
        </div>
    );
}

function FileIcon() {
    return (
        <svg className="w-4 h-4 text-[#0096D6] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
        </svg>
    );
}

function DownloadIcon() {
    return (
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
    );
}

function TrashIcon() {
    return (
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
        </svg>
    );
}
