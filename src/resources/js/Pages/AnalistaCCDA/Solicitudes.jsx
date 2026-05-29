import { Head, usePage, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';

const ESTADO_BADGE = {
    pendiente_aprobacion: 'bg-yellow-100 text-yellow-800',
    activa:               'bg-amber-100 text-amber-800',
    cerrada:              'bg-gray-100 text-gray-600',
    rechazada:            'bg-red-100 text-red-700',
};

function SolicitudCard({ s, actions }) {
    return (
        <div className={`bg-white rounded-xl border p-4 ${
            s.estado === 'activa' ? 'border-amber-300' : 'border-gray-200'
        }`}>
            <div className="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <div className="flex items-center gap-2 flex-wrap">
                        <span className={`text-xs font-semibold px-2 py-0.5 rounded-full ${ESTADO_BADGE[s.estado] ?? 'bg-gray-100 text-gray-600'}`}>
                            {s.estado_label}
                        </span>
                        <span className="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">
                            {s.tipo_label}
                        </span>
                        {s.tipo === 'licencia_medica' && s.estado === 'activa' && (
                            <span className="text-xs bg-red-50 text-red-700 px-2 py-0.5 rounded-full">
                                Acceso bloqueado
                            </span>
                        )}
                    </div>
                    <p className="font-semibold text-gray-900 text-sm mt-2">{s.academico.name}</p>
                    <p className="text-xs text-gray-500">{s.academico.rut} · {s.academico.facultad}</p>
                    <p className="text-xs text-gray-500 mt-1">
                        {s.fecha_inicio}{s.fecha_fin ? ` → ${s.fecha_fin}` : ''}
                    </p>
                    <p className="text-sm text-gray-600 mt-2">{s.motivo}</p>
                    {s.iniciada_por && (
                        <p className="text-xs text-gray-400 mt-1">Iniciada por: {s.iniciada_por}</p>
                    )}
                    {s.motivo_rechazo && (
                        <p className="text-xs text-red-600 mt-1">Rechazo: {s.motivo_rechazo}</p>
                    )}
                    {s.estado === 'cerrada' && s.fecha_reincorporacion && (
                        <div className="mt-2 pt-2 border-t border-gray-100 text-xs text-gray-500 space-y-0.5">
                            <p>Reincorporado: {s.fecha_reincorporacion} por {s.reincorporado_por}</p>
                            <p>Nuevo plazo evidencias: {s.nuevo_plazo_evidencias}</p>
                            {s.motivo_reincorporacion && (
                                <p>Motivo: {s.motivo_reincorporacion}</p>
                            )}
                        </div>
                    )}
                </div>
                <div className="flex flex-col gap-2 items-end">
                    {s.documento_url && (
                        <a href={s.documento_url} className="text-xs text-[#0096D6] hover:underline">
                            Ver documento
                        </a>
                    )}
                    {actions}
                </div>
            </div>
        </div>
    );
}

function ModalReincorporar({ solicitud, plazo, motivo, processing, onPlazoChange, onMotivoChange, onConfirm, onCancel }) {
    if (!solicitud) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div className="absolute inset-0 bg-black/40" onClick={onCancel} aria-hidden="true" />
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-reincorporar-title"
                className="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6"
            >
                <h3 id="modal-reincorporar-title" className="text-base font-semibold text-gray-900">
                    Reincorporar a {solicitud.academico.name}
                </h3>
                <p className="text-sm text-gray-500 mt-1">
                    Defina los nuevos plazos para este académico. Serán independientes del resto de la nómina.
                </p>

                <div className="mt-5 space-y-4">
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            Nueva fecha límite de carga de evidencias <span className="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            value={plazo}
                            onChange={e => onPlazoChange(e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30 focus:border-[#1B2D6B]"
                            autoFocus
                        />
                    </div>

                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">
                            Motivo de reincorporación <span className="text-gray-400">(opcional)</span>
                        </label>
                        <textarea
                            rows={3}
                            value={motivo}
                            onChange={e => onMotivoChange(e.target.value)}
                            placeholder="Ej.: Fin de licencia médica, retoma actividades docentes..."
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30 focus:border-[#1B2D6B]"
                        />
                    </div>
                </div>

                <div className="mt-6 flex gap-3 justify-end">
                    <button
                        type="button"
                        onClick={onCancel}
                        disabled={processing}
                        className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        onClick={onConfirm}
                        disabled={processing || !plazo}
                        className="px-4 py-2 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                    >
                        {processing ? 'Procesando...' : 'Confirmar reincorporación'}
                    </button>
                </div>
            </div>
        </div>
    );
}

export default function Solicitudes({ periodo, pendientes, historial }) {
    const { flash } = usePage().props;
    const [rechazandoId, setRechazandoId] = useState(null);
    const [motivoRechazo, setMotivoRechazo] = useState('');
    const [modalSolicitud, setModalSolicitud] = useState(null);
    const [plazoReincorporacion, setPlazoReincorporacion] = useState('');
    const [motivoReincorporacion, setMotivoReincorporacion] = useState('');
    const [processingReincorporacion, setProcessingReincorporacion] = useState(false);

    function aprobar(id) {
        router.patch(`/analista/solicitudes/${id}/aprobar`, {}, { preserveScroll: true });
    }

    function rechazar(id) {
        router.patch(`/analista/solicitudes/${id}/rechazar`, {
            motivo_rechazo: motivoRechazo,
        }, {
            preserveScroll: true,
            onFinish: () => { setRechazandoId(null); setMotivoRechazo(''); },
        });
    }

    function abrirModalReincorporar(solicitud) {
        setModalSolicitud(solicitud);
        setPlazoReincorporacion('');
        setMotivoReincorporacion('');
    }

    function cerrarModalReincorporar() {
        if (processingReincorporacion) return;
        setModalSolicitud(null);
        setPlazoReincorporacion('');
        setMotivoReincorporacion('');
    }

    function confirmarReincorporacion() {
        if (!modalSolicitud || !plazoReincorporacion) return;

        setProcessingReincorporacion(true);
        router.patch(`/analista/solicitudes/${modalSolicitud.id}/reincorporar`, {
            nuevo_plazo_evidencias: plazoReincorporacion,
            motivo_reincorporacion: motivoReincorporacion || null,
        }, {
            preserveScroll: true,
            onFinish: () => {
                setProcessingReincorporacion(false);
                cerrarModalReincorporar();
            },
        });
    }

    return (
        <>
            <Head title="Solicitudes y Excepciones" />
            <AppLayout title="Solicitudes y Excepciones">

                {flash?.success && (
                    <div className="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        {flash.error}
                    </div>
                )}

                {!periodo ? (
                    <div className="bg-white rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-400 text-sm">
                        No hay período activo.
                    </div>
                ) : (
                    <div className="space-y-8">

                        <section>
                            <h2 className="text-sm font-semibold text-gray-700 mb-3">
                                Solicitudes pendientes ({pendientes.length})
                            </h2>
                            <p className="text-xs text-gray-400 mb-4">
                                Iniciadas por secretarios de facultad. Apruebe o rechace para continuar el flujo.
                            </p>

                            {pendientes.length === 0 ? (
                                <div className="bg-white rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-400 text-sm">
                                    No hay solicitudes pendientes de aprobación.
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {pendientes.map(s => (
                                        <SolicitudCard key={s.id} s={s} actions={
                                            rechazandoId === s.id ? (
                                                <div className="flex flex-col gap-2 items-end max-w-xs">
                                                    <textarea rows={2} value={motivoRechazo}
                                                        onChange={e => setMotivoRechazo(e.target.value)}
                                                        placeholder="Motivo del rechazo..."
                                                        className="w-full border border-gray-300 rounded px-2 py-1 text-xs resize-none" />
                                                    <div className="flex gap-2">
                                                        <button onClick={() => rechazar(s.id)}
                                                            disabled={motivoRechazo.length < 10}
                                                            className="text-xs bg-red-600 text-white px-3 py-1 rounded disabled:opacity-40">
                                                            Confirmar rechazo
                                                        </button>
                                                        <button onClick={() => setRechazandoId(null)}
                                                            className="text-xs text-gray-400">Cancelar</button>
                                                    </div>
                                                </div>
                                            ) : (
                                                <div className="flex gap-2">
                                                    <button onClick={() => aprobar(s.id)}
                                                        className="text-xs font-medium bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                                                        Aprobar
                                                    </button>
                                                    <button onClick={() => setRechazandoId(s.id)}
                                                        className="text-xs font-medium text-red-600 hover:underline">
                                                        Rechazar
                                                    </button>
                                                </div>
                                            )
                                        } />
                                    ))}
                                </div>
                            )}
                        </section>

                        <section>
                            <h2 className="text-sm font-semibold text-gray-700 mb-3">
                                Historial de solicitudes ({historial.length})
                            </h2>

                            {historial.length === 0 ? (
                                <div className="bg-white rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-400 text-sm">
                                    No hay solicitudes en el historial.
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {historial.map(s => (
                                        <SolicitudCard key={s.id} s={s} actions={
                                            s.estado === 'activa' ? (
                                                <button
                                                    onClick={() => abrirModalReincorporar(s)}
                                                    className="text-xs font-medium text-green-700 hover:underline"
                                                >
                                                    Cerrar / Reincorporar
                                                </button>
                                            ) : null
                                        } />
                                    ))}
                                </div>
                            )}
                        </section>
                    </div>
                )}

                <ModalReincorporar
                    solicitud={modalSolicitud}
                    plazo={plazoReincorporacion}
                    motivo={motivoReincorporacion}
                    processing={processingReincorporacion}
                    onPlazoChange={setPlazoReincorporacion}
                    onMotivoChange={setMotivoReincorporacion}
                    onConfirm={confirmarReincorporacion}
                    onCancel={cerrarModalReincorporar}
                />
            </AppLayout>
        </>
    );
}
