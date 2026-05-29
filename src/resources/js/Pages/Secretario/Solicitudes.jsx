import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

const ESTADO_BADGE = {
    pendiente_aprobacion: 'bg-yellow-100 text-yellow-800',
    activa:               'bg-amber-100 text-amber-800',
    cerrada:              'bg-gray-100 text-gray-600',
    rechazada:            'bg-red-100 text-red-700',
};

export default function Solicitudes({ periodo, solicitudes, nominas }) {
    const { flash } = usePage().props;

    const form = useForm({
        nomina_id:    '',
        tipo:         'licencia_medica',
        fecha_inicio: '',
        fecha_fin:    '',
        motivo:       '',
        documento:    null,
    });

    function submit(e) {
        e.preventDefault();
        form.post('/secretario/solicitudes', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }

    return (
        <>
            <Head title="Solicitudes" />
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
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        <div className="bg-white rounded-xl border border-gray-200 p-5">
                            <h2 className="text-sm font-semibold text-gray-700 mb-1">Iniciar solicitud</h2>
                            <p className="text-xs text-gray-400 mb-4">
                                Registre la solicitud recibida del director de departamento. Quedará pendiente de aprobación CCDA.
                            </p>

                            <form onSubmit={submit} className="space-y-4">
                                <div>
                                    <label className="block text-xs font-medium text-gray-600 mb-1">Académico (nómina)</label>
                                    <select value={form.data.nomina_id}
                                        onChange={e => form.setData('nomina_id', e.target.value)}
                                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        <option value="">— Seleccionar —</option>
                                        {nominas.map(n => (
                                            <option key={n.id} value={n.id}>{n.label}</option>
                                        ))}
                                    </select>
                                    {form.errors.nomina_id && (
                                        <p className="text-xs text-red-600 mt-1">{form.errors.nomina_id}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                                    <select value={form.data.tipo}
                                        onChange={e => form.setData('tipo', e.target.value)}
                                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        <option value="licencia_medica">Licencia médica</option>
                                        <option value="extension_plazo">Extensión de plazo</option>
                                    </select>
                                </div>

                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <label className="block text-xs font-medium text-gray-600 mb-1">Fecha inicio</label>
                                        <input type="date" value={form.data.fecha_inicio}
                                            onChange={e => form.setData('fecha_inicio', e.target.value)}
                                            className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-gray-600 mb-1">Fecha fin (opc.)</label>
                                        <input type="date" value={form.data.fecha_fin}
                                            onChange={e => form.setData('fecha_fin', e.target.value)}
                                            className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-gray-600 mb-1">Motivo</label>
                                    <textarea rows={3} value={form.data.motivo}
                                        onChange={e => form.setData('motivo', e.target.value)}
                                        placeholder="Indique el motivo informado por el director de departamento..."
                                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none" />
                                    {form.errors.motivo && (
                                        <p className="text-xs text-red-600 mt-1">{form.errors.motivo}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-gray-600 mb-1">Documento adjunto</label>
                                    <p className="text-xs text-gray-400 mb-1">Respaldo del correo del director (PDF o imagen)</p>
                                    <input type="file" accept=".pdf,.jpg,.jpeg,.png"
                                        onChange={e => form.setData('documento', e.target.files[0])}
                                        className="w-full text-sm" />
                                </div>

                                <button type="submit" disabled={form.processing}
                                    className="w-full bg-[#1B2D6B] text-white text-sm font-medium py-2.5 rounded-lg hover:bg-[#152558] disabled:opacity-50">
                                    {form.processing ? 'Enviando...' : 'Enviar al CCDA'}
                                </button>
                            </form>
                        </div>

                        <div className="lg:col-span-2 space-y-3">
                            <h2 className="text-sm font-semibold text-gray-700">
                                Mis solicitudes ({solicitudes.length})
                            </h2>

                            {solicitudes.length === 0 ? (
                                <div className="bg-white rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-400 text-sm">
                                    Aún no ha registrado solicitudes en este período.
                                </div>
                            ) : solicitudes.map(s => (
                                <div key={s.id} className="bg-white rounded-xl border border-gray-200 p-4">
                                    <div className="flex flex-wrap items-start justify-between gap-2">
                                        <div>
                                            <div className="flex items-center gap-2 flex-wrap">
                                                <span className={`text-xs font-semibold px-2 py-0.5 rounded-full ${ESTADO_BADGE[s.estado] ?? 'bg-gray-100'}`}>
                                                    {s.estado_label}
                                                </span>
                                                <span className="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">
                                                    {s.tipo_label}
                                                </span>
                                                {s.estado === 'pendiente_aprobacion' && (
                                                    <span className="text-xs text-yellow-700">Esperando aprobación CCDA</span>
                                                )}
                                            </div>
                                            <p className="font-semibold text-gray-900 text-sm mt-2">{s.academico.name}</p>
                                            <p className="text-xs text-gray-500">{s.academico.rut}</p>
                                            <p className="text-xs text-gray-500 mt-1">
                                                {s.fecha_inicio}{s.fecha_fin ? ` → ${s.fecha_fin}` : ''}
                                            </p>
                                            <p className="text-sm text-gray-600 mt-2">{s.motivo}</p>
                                            {s.motivo_rechazo && (
                                                <p className="text-xs text-red-600 mt-2">
                                                    <strong>Motivo rechazo CCDA:</strong> {s.motivo_rechazo}
                                                </p>
                                            )}
                                            {s.aprobada_por && s.estado !== 'pendiente_aprobacion' && (
                                                <p className="text-xs text-gray-400 mt-1">
                                                    Revisada por: {s.aprobada_por} · {s.fecha_aprobacion}
                                                </p>
                                            )}
                                        </div>
                                        {s.documento_url && (
                                            <a href={s.documento_url} className="text-xs text-[#0096D6] hover:underline">
                                                Ver documento
                                            </a>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </AppLayout>
        </>
    );
}
