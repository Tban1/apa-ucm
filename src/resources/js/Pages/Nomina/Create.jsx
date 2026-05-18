import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import AppLayout from '@/Layouts/AppLayout';

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const [y, m, d] = dateStr.split('T')[0].split('-');
    return `${d}/${m}/${y}`;
}

export default function NominaCreate({ periodo, facultades, academicos, nominasEnPeriodo }) {
    const { flash } = usePage().props;
    const [facultadId, setFacultadId]           = useState('');
    const [editingId, setEditingId]             = useState(null); // nomina.id en edición de licencia
    const [obsInput, setObsInput]               = useState('');
    const [savingId, setSavingId]               = useState(null); // nomina.id con request en vuelo

    const { data, setData, post, processing, errors } = useForm({ user_ids: [] });

    // Map user_id → nomina completa
    const nominaMap = useMemo(() => {
        const m = {};
        nominasEnPeriodo.forEach(n => { m[n.user_id] = n; });
        return m;
    }, [nominasEnPeriodo]);

    const yaEnSet = useMemo(
        () => new Set(nominasEnPeriodo.map(n => n.user_id)),
        [nominasEnPeriodo]
    );

    const academicosDeFactultad = useMemo(
        () => academicos.filter(a => a.facultad_id === facultadId),
        [academicos, facultadId]
    );

    const pendientes = useMemo(
        () => academicosDeFactultad.filter(a => !yaEnSet.has(a.id)),
        [academicosDeFactultad, yaEnSet]
    );

    const enNomina = useMemo(
        () => academicosDeFactultad.filter(a => yaEnSet.has(a.id)),
        [academicosDeFactultad, yaEnSet]
    );

    /* ── Nómina principal ─────────────────────────────────── */
    function toggleAcademico(userId) {
        const current = data.user_ids;
        setData('user_ids',
            current.includes(userId)
                ? current.filter(id => id !== userId)
                : [...current, userId]
        );
    }
    function selectAll()  { setData('user_ids', pendientes.map(a => a.id)); }
    function clearAll()   { setData('user_ids', []); }
    function onFacultadChange(id) { setFacultadId(id); setData('user_ids', []); setEditingId(null); }
    function submitNomina(e) { e.preventDefault(); post(`/analista/periodos/${periodo.id}/nominas`); }

    /* ── Caso especial (licencia) ─────────────────────────── */
    function abrirLicencia(nominaId) {
        setEditingId(nominaId);
        setObsInput('');
    }

    function confirmarLicencia(nominaId) {
        setSavingId(nominaId);
        router.patch(
            `/analista/nominas/${nominaId}/licencia`,
            { con_licencia: true, observacion_licencia: obsInput },
            {
                preserveScroll: true,
                onFinish: () => setSavingId(null),
                onSuccess: () => { setEditingId(null); setObsInput(''); },
            }
        );
    }

    function quitarLicencia(nominaId) {
        setSavingId(nominaId);
        router.patch(
            `/analista/nominas/${nominaId}/licencia`,
            { con_licencia: false, observacion_licencia: null },
            { preserveScroll: true, onFinish: () => setSavingId(null) }
        );
    }

    const totalNomina = nominasEnPeriodo.length;
    const totalLicencias = nominasEnPeriodo.filter(n => n.con_licencia).length;

    return (
        <>
            <Head title={`Nómina — ${periodo.nombre}`} />
            <AppLayout title="Gestionar Nómina">

                {/* Breadcrumb */}
                <div className="flex items-center gap-2 text-sm text-gray-500 -mt-4 mb-6">
                    <Link href="/analista/periodos" className="hover:text-gray-700">Períodos</Link>
                    <span>/</span>
                    <span className="text-gray-700 font-medium">{periodo.nombre}</span>
                    <span>/</span>
                    <span>Nómina</span>
                </div>

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

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {/* Panel izquierdo */}
                    <div className="space-y-5">
                        <div className="bg-white rounded-xl border border-gray-200 p-5">
                            <p className="text-xs text-gray-400 uppercase tracking-wide mb-1">Período</p>
                            <p className="font-semibold text-gray-900">{periodo.nombre}</p>
                            <p className="text-sm text-gray-500 mt-1">Año {periodo.anio}</p>
                            <div className="mt-3 pt-3 border-t border-gray-100 space-y-2">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-gray-500">Total en nómina</span>
                                    <span className="text-lg font-bold text-[#1B2D6B]">{totalNomina}</span>
                                </div>
                                {totalLicencias > 0 && (
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-gray-500">Casos especiales</span>
                                        <span className="text-sm font-semibold text-amber-600">{totalLicencias}</span>
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="bg-white rounded-xl border border-gray-200 p-5">
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Seleccionar facultad
                            </label>
                            <select
                                value={facultadId}
                                onChange={e => onFacultadChange(e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1B2D6B]/30 focus:border-[#1B2D6B]"
                            >
                                <option value="">— Selecciona una facultad —</option>
                                {facultades.map(f => (
                                    <option key={f.id} value={f.id}>{f.nombre}</option>
                                ))}
                            </select>
                        </div>
                    </div>

                    {/* Panel derecho */}
                    <div className="lg:col-span-2">
                        {!facultadId ? (
                            <div className="bg-white rounded-xl border border-dashed border-gray-300 p-10 text-center h-full flex items-center justify-center">
                                <p className="text-gray-400 text-sm">Selecciona una facultad para ver sus académicos.</p>
                            </div>
                        ) : (
                            <form onSubmit={submitNomina} className="space-y-4">

                                {/* Académicos disponibles */}
                                <div className="bg-white rounded-xl border border-gray-200">
                                    <div className="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                                        <p className="text-sm font-semibold text-gray-700">
                                            Académicos disponibles
                                            {pendientes.length > 0 && (
                                                <span className="ml-2 text-xs font-normal text-gray-400">({pendientes.length})</span>
                                            )}
                                        </p>
                                        {pendientes.length > 0 && (
                                            <div className="flex gap-3">
                                                <button type="button" onClick={selectAll}
                                                    className="text-xs text-[#0096D6] hover:underline">
                                                    Seleccionar todos
                                                </button>
                                                {data.user_ids.length > 0 && (
                                                    <button type="button" onClick={clearAll}
                                                        className="text-xs text-gray-400 hover:text-gray-600 hover:underline">
                                                        Limpiar
                                                    </button>
                                                )}
                                            </div>
                                        )}
                                    </div>

                                    {pendientes.length === 0 ? (
                                        <p className="px-5 py-6 text-sm text-gray-400 text-center">
                                            Todos los académicos de esta facultad ya están en la nómina.
                                        </p>
                                    ) : (
                                        <ul className="divide-y divide-gray-50">
                                            {pendientes.map(a => (
                                                <li key={a.id}
                                                    className="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 cursor-pointer"
                                                    onClick={() => toggleAcademico(a.id)}
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={data.user_ids.includes(a.id)}
                                                        onChange={() => toggleAcademico(a.id)}
                                                        className="h-4 w-4 rounded border-gray-300 text-[#1B2D6B] focus:ring-[#1B2D6B]/30"
                                                        onClick={e => e.stopPropagation()}
                                                    />
                                                    <div className="flex-1">
                                                        <p className="text-sm font-medium text-gray-800">{a.name}</p>
                                                        {a.rut && <p className="text-xs text-gray-400">{a.rut}</p>}
                                                    </div>
                                                </li>
                                            ))}
                                        </ul>
                                    )}
                                </div>

                                {errors.user_ids && (
                                    <p className="text-xs text-red-600">{errors.user_ids}</p>
                                )}

                                {pendientes.length > 0 && (
                                    <div className="flex items-center gap-4">
                                        <button
                                            type="submit"
                                            disabled={processing || data.user_ids.length === 0}
                                            className="bg-[#1B2D6B] text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-[#152558] disabled:opacity-50 transition-colors"
                                        >
                                            {processing
                                                ? 'Agregando...'
                                                : `Agregar${data.user_ids.length > 0 ? ` ${data.user_ids.length}` : ''} a la nómina`
                                            }
                                        </button>
                                        <Link href="/analista/periodos"
                                            className="text-sm text-gray-500 hover:text-gray-700">
                                            Volver a períodos
                                        </Link>
                                    </div>
                                )}

                                {/* Académicos ya en nómina + gestión de casos especiales */}
                                {enNomina.length > 0 && (
                                    <div className="bg-white rounded-xl border border-gray-200">
                                        <div className="px-5 py-3 border-b border-gray-100">
                                            <p className="text-sm font-semibold text-gray-700">
                                                Ya en la nómina
                                                <span className="ml-2 text-xs font-normal text-gray-400">({enNomina.length})</span>
                                            </p>
                                        </div>
                                        <ul className="divide-y divide-gray-100">
                                            {enNomina.map(a => {
                                                const nom       = nominaMap[a.id];
                                                const isEditing = editingId === nom?.id;
                                                const isSaving  = savingId === nom?.id;

                                                return (
                                                    <li key={a.id} className="px-5 py-3">
                                                        <div className="flex items-center gap-3">
                                                            <input type="checkbox" checked disabled
                                                                className="h-4 w-4 rounded border-gray-300 opacity-50" />
                                                            <div className="flex-1 min-w-0">
                                                                <p className="text-sm font-medium text-gray-800 truncate">{a.name}</p>
                                                                {a.rut && <p className="text-xs text-gray-400">{a.rut}</p>}
                                                            </div>

                                                            {/* Badge + acciones de licencia */}
                                                            <div className="flex items-center gap-2 shrink-0">
                                                                {nom?.con_licencia ? (
                                                                    <>
                                                                        <span className="inline-flex items-center gap-1 text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full">
                                                                            Caso especial
                                                                        </span>
                                                                        <button
                                                                            type="button"
                                                                            disabled={isSaving}
                                                                            onClick={() => quitarLicencia(nom.id)}
                                                                            className="text-xs text-gray-400 hover:text-red-500 disabled:opacity-40 transition-colors"
                                                                        >
                                                                            {isSaving ? '...' : 'Quitar'}
                                                                        </button>
                                                                    </>
                                                                ) : (
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => abrirLicencia(nom?.id)}
                                                                        className="text-xs text-amber-600 hover:text-amber-800 font-medium transition-colors"
                                                                    >
                                                                        + Caso especial
                                                                    </button>
                                                                )}
                                                            </div>
                                                        </div>

                                                        {/* Observación registrada */}
                                                        {nom?.con_licencia && nom?.observacion_licencia && !isEditing && (
                                                            <div className="mt-1.5 ml-7 text-xs text-gray-500 bg-amber-50 rounded-md px-3 py-1.5">
                                                                <span className="font-medium text-amber-700">Motivo:</span> {nom.observacion_licencia}
                                                                <span className="ml-2 text-gray-400">· {formatDate(nom.updated_at)}</span>
                                                            </div>
                                                        )}

                                                        {/* Formulario inline para activar licencia */}
                                                        {isEditing && (
                                                            <div className="mt-2 ml-7 space-y-2">
                                                                <input
                                                                    type="text"
                                                                    value={obsInput}
                                                                    onChange={e => setObsInput(e.target.value)}
                                                                    placeholder="Motivo del caso especial (ej: licencia médica)"
                                                                    className="w-full border border-amber-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-200 focus:border-amber-400"
                                                                    autoFocus
                                                                />
                                                                <div className="flex items-center gap-3">
                                                                    <button
                                                                        type="button"
                                                                        disabled={!obsInput.trim() || isSaving}
                                                                        onClick={() => confirmarLicencia(nom.id)}
                                                                        className="text-xs font-medium bg-amber-500 text-white px-3 py-1.5 rounded-lg hover:bg-amber-600 disabled:opacity-50 transition-colors"
                                                                    >
                                                                        {isSaving ? 'Guardando...' : 'Confirmar'}
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => setEditingId(null)}
                                                                        className="text-xs text-gray-400 hover:text-gray-600"
                                                                    >
                                                                        Cancelar
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        )}
                                                    </li>
                                                );
                                            })}
                                        </ul>
                                    </div>
                                )}
                            </form>
                        )}
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
